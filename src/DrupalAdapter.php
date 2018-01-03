<?php

namespace FRNApp;

class DrupalAdapter
{
    protected $path;
    protected $url;

    public function __construct($path, $url)
    {
        $this->path = $path;
        $this->url = $url;
    }

    public function getShows($limit = NULL, $offset = 0, $id = NULL)
    {
        $shows = [];
        $this->bootDrupal();

        $this->field_name = 'field_series_showtime';
        $this->field_info = field_info_field($this->field_name);
        $this->timezone_db = date_get_timezone_db($this->field_info['settings']['tz_handling']);
        $this->db_format = date_type_format($this->field_info['type']);
        $this->timezone = date_get_timezone($this->field_info['settings']['tz_handling'], '');
        $this->tz = new \DateTimeZone($this->timezone);

        $q = "SELECT * FROM {node} n 
          LEFT JOIN {field_data_field_series_showtime} s 
            ON  n.nid = s.entity_id 
            AND s.entity_type = 'node'
            AND s.delta = 0
          LEFT JOIN {field_data_body} b
            ON  n.nid = b.entity_id 
            AND s.entity_type = 'node'
            AND s.delta = 0
          WHERE n.status = 1
            AND n.type = 'series'
            AND s.field_series_showtime_rrule IS NOT NULL";

        if (!empty($id)) {
            $q .= ' AND n.nid = :id ';
        }

        if (!empty($limit)) {
            $q .= "LIMIT $limit OFFSET $offset";
        }


        $rows = db_query($q, array(':id' => $id));


        foreach ($rows as $row) {
            $show = $this->convertRow($row);
            if ($rerun = $this->getRerun($show)) {
                $show->rerun = $this->convertRow($rerun, TRUE);
            }
            $shows[] = $show;
//            dump($shows);
        }

        return $shows;
    }

    public function getRerun($show) {
        $q = "SELECT * FROM {node} n 
          LEFT JOIN {field_data_field_series_showtime} s 
            ON  n.nid = s.entity_id 
            AND s.entity_type = 'node'
            AND s.delta = 0
          LEFT JOIN {field_data_field_series} r
            ON  n.nid = r.entity_id 
            AND r.entity_type = 'node'
            AND r.delta = 0
            AND r.field_series_target_id = :target
          WHERE n.status = 1
            AND n.type = 'series_rerun'
            AND s.field_series_showtime_rrule IS NOT NULL 
          LIMIT 1";

        $res = db_query($q, array(':target' => $show->originalId));
        foreach ($res as $row) {
            return $row;
        }
    }

    public function convertRow($row, $rerun = FALSE) {
        $show = new \stdClass();
        $show->rrule = $row->field_series_showtime_rrule;
        $show->start = $row->field_series_showtime_value;
        $show->end = $row->field_series_showtime_value2;
        foreach (['start', 'end'] as $key) {
            $date = new \DateObject($show->{$key}, $this->timezone_db, $this->db_format);
            date_timezone_set($date, $this->tz);
            $show->{$key} = $date;
        }

        if (!$rerun) {
            $show->title = $row->title;
            $show->originalId = $row->nid;
            $show->url = url('node/' . $row->nid, ['absolute' => TRUE]);
            $show->feed_url = url('node/' . $row->nid . '/feed', ['absolute' => TRUE]);

            $body = !empty($row->body_summary) ? $row->body_summary : $row->body_value;
            $body = strip_tags($body);
            $show->body = $body;

        }
        return $show;
    }

    protected function bootDrupal()
    {
        $cwd = getcwd();

        chdir($this->path);
        define('DRUPAL_ROOT', $this->path);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['base_url'] = $this->url;
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        chdir($cwd);
    }
}