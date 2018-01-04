<?php

namespace FRNApp;

class DrupalAdapter
{
    protected $path;
    protected $url;
    protected $urlOpts;

    public function __construct($path, $url)
    {
        $this->path = $path;
        $this->url = $url;
        $this->urlOpts = ['absolute' => TRUE, 'https' => TRUE];
        $this->bootDrupal();
    }

    public function getShows($limit = NULL, $offset = 0, $id = NULL)
    {
        $shows = [];
        return $shows;
    }

    public function renderBody($row) {
        $body = !empty($row->body_summary) ? $row->body_summary : $row->body_value;
        return $this->formatString($body);
    }

    public function formatString($string) {
        $string = strip_tags($string,  '<br><br/><p>');
        $string = str_replace('&nbsp;', ' ', $string);
        $string = str_replace('</p>', '', $string);
        $string = preg_replace('/<p[^>]*>/', "\n", $string);
        $string = preg_replace('/<br[^>]*>/', "\n", $string);
//            $string = drupal_html_to_text($string, ['b', 'p']);
        $string = preg_replace('/ +/', ' ', $string);
        $string = preg_replace('/\[\[.*?\]\]/ms', '', $string);
        $string = preg_replace("/\n(\s*\n)+/", "\n", $string);
        return $string;
    }


    protected function bootDrupal()
    {
        $cwd = getcwd();

        chdir($this->path);
        define('DRUPAL_ROOT', $this->path);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['base_url'] = $GLOBALS['base_insecure_url'] = 'http://' . $this->url;
        $GLOBALS['base_secure_url'] = 'https://' . $this->url;
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        chdir($cwd);
    }
}
