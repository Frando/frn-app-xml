<?php

namespace FRNApp;

class DrupalAdapterFreeFM extends DrupalAdapter
{
    protected $path;
    protected $url;
    protected $urlOpts;

    public function __construct($path, $url)
    {
        parent::__construct($path, $url);
    }

    public function getShows($limit = NULL, $offset = 0, $id = NULL)
    {
        $shows = [];
        $view_name = 'frontpage_sendungen';
        $result = views_get_view_result($view_name, 'default');
        $nids = array_map(function($val) { return $val->nid; }, $result);
        $nodes = node_load_multiple($nids);
        foreach ($nodes as $node)  {
            $show = new \stdClass();
            $this->currentId = $node->nid;
            $show->title = $node->title;
            $show->originalId = $node->nid;
            $show->url = url('node/' . $node->nid, $this->urlOpts);
//            $show->feed_url = url('node/' . $node->nid . '/feed', $this->urlOpts);
            $body = $node->body['und'][0];
            $body_text = !empty($body['summary']) ? $body['summary'] : $body['value'];
            if (empty($body_text)) {
                $body_text = "";
            }
            $show->body = $this->formatString($body_text);
            $show->airtime = $this->getAirtime($node);
            $shows[] = $show;
        }

        return $shows;
    }

    protected function getAirtime($node) {
        $airtime = [];
        if (empty($node->field_airtime)) {
            return $airtime;
        }
        foreach ($node->field_airtime['und'] as $row) {
            $airobj = $this->parseAirtime($row['value']);
            $airtime[] = $airobj;
        }
        return $airtime;
    }

    protected function parseAirtime($string) {
        $airobj = airplan_parse_string($string);
        $odd = -1;
        $airobj->type = 'week';
        if ($airobj->params == 'ODD') {
            $odd = TRUE;
        }
        else if ($airobj->params == 'EVEN') {
            $odd = FALSE;
        }

        if ($odd !== -1) {
            if (variable_get("airplan_invert", FALSE)) {
                $odd = !$odd;
            }
            $airobj->odd = $odd;
            $airobj->type = 'secondweek';
        }
        else if ($airobj->params == 'LAST') {
            $airobj->type = 'last';
        }
        else if ($airobj->params == 'NLAST') {
            $airobj->type = 'nlast';
        }
        else if (preg_match('/^[01]{5}$/', $airobj->params)) {
            $weeknumbers = array();
            foreach (str_split($airobj->params) as $i => $has) {
                if ($has) {
                    $weeknumbers[] = ($i + 1); // +1 because $i starts at 0;
                }
            }
            $airobj->type = 'weeknums';
            $airobj->weeknums = $weeknumbers;
        }
        else if (preg_match('/!?[0-9]{8}/', $airobj->params)) {
            // Exclusion/inclusion unsupported for now.
            $airobj->type = 'unsupported';
        }
        else if (!empty($airobj->params)) {
            $airobj->type = 'unsupported';
            dump("unsupported params found for show " . $this->currentId);
            dump($airobj->params);
        }

        return $airobj;
    }

}
