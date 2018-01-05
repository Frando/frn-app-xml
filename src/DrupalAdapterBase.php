<?php

namespace FRNApp;

use Symfony\Component\Console\Exception\RuntimeException;

abstract class DrupalAdapterBase implements AdapterInterface
{
    protected $path;
    protected $url;
    protected $urlOpts;

    public function __construct($opts = array())
    {
        $this->path = isset($opts['path']) ? $opts['path'] : '.';
        $this->url = isset($opts['url']) ? $opts['url'] : 'localhost';
        $this->urlOpts = ['absolute' => TRUE, 'https' => TRUE];
        $this->bootDrupal();
    }

    /**
     * Get broadcasted shows.
     *
     * @param array|null $ids Array of IDs to limit output. NULL for no limit.
     * @return array Array of objects containing all broadcast information needed for XML creation.
     */
    abstract public function getBroadcasts($ids = NULL);

    protected function renderBody($row) {
        $body = !empty($row->body_summary) ? $row->body_summary : $row->body_value;
        return $this->formatString($body);
    }

    protected function formatString($string) {
        // Strip tags (all but line changes);
        $string = strip_tags($string,  '<br><br/><p>');
        // Convert <p> and <br> to newlines.
        $string = str_replace('</p>', '', $string);
        $string = preg_replace('/<p[^>]*>/', "\n", $string);
        $string = preg_replace('/<br[^>]*>/', "\n", $string);
        // Remove double spaces.
        $string = str_replace('&nbsp;', ' ', $string);
        $string = preg_replace('/ +/', ' ', $string);
        // Remove anything in double square brackets.
        $string = preg_replace('/\[\[.*?\]\]/ms', '', $string);
        // Remove double newlines.
        $string = preg_replace("/\n(\s*\n)+/", "\n", $string);
        // Encode entities.
        $string = htmlentities($string, ENT_COMPAT | ENT_XML1, 'UTF-8', FALSE);
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
        if (!file_exists(DRUPAL_ROOT . '/includes/bootstrap.inc')) {
            throw new RuntimeException("No Drupal root found at " . realpath($this->path));
        }
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        chdir($cwd);
    }
}
