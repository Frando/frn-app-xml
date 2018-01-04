<?php

namespace FRNApp;

use FRNApp\DrupalAdapter;

abstract class XmlCreatorBase {
    /** @var  DrupalAdapter */
    public $adapter;
    public $opts;
    public $idLimit;
    public $savePath;

    /** @var  \DOMDocument */
    public $doc;

    /** @var  array */
    public $shows;

    /** @var \DOMElement */
    public $info;

    /** @var \DOMElement */
    public $channels;

    /** @var \DOMElement */
    public $station;

    /** @var \DOMElement */
    public $programme;

    public function __construct($adapter, $opts = array()) {
        $this->adapter = $adapter;
        $this->opts = $opts;
        $this->savePath = 'data/frn.xml';
        $this->idLimit = [];
    }

    public function setIdLimit($ids) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $this->idLimit = $ids;
    }

    public function createXml() {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $this->doc = $doc;

        $now = new \DateTime();
        $this->station = $this->el('station', NULL, [
                'lastupdate' => $now->format('c'),
                'xmlns:gml' => "http://www.opengis.net/gml"]
        );
        $doc->appendChild($this->station);

        $this->info = $this->getInfo();
        $this->station->appendChild($this->info);
        $this->channels = $this->getMediaChannels();
        $this->station->appendChild($this->channels);

        $this->programme = $doc->createElement('programme');
        $this->station->appendChild($this->programme);

        $this->shows = $this->adapter->getShows(0, 0, $this->idLimit);
        $id = 1;
        foreach ($this->shows as $show) {
            $broadcast = $this->getBroadcast($id, $show);
            if (!empty($broadcast)) {
                $this->programme->appendChild($this->getBroadcast($id, $show));
                $id++;
            }
        }
        $doc->formatOutput = TRUE;
        $xml = $doc->saveXML();
        file_put_contents($this->savePath, $xml);
    }

    public function getBroadcast($id, $info) {
        $broadcast = $this->el('broadcast', NULL, ['id' => $id]);
        return $broadcast;
    }

    public function getMediaChannels()
    {
        $el = $this->el('media-channels');
        return $el;
    }

    public function getInfo()
    {
        $el = $this->el('info');
        return $el;
    }

    protected function el($name, $val = NULL, $attrs = [])
    {
        $el = $this->doc->createElement($name, $val);
        if (!empty($attrs)) {
            foreach ($attrs as $key => $val) {
                $el->setAttribute($key, $val);
            }
        }
        return $el;
    }

    public function elToString($el)
    {
        $el = clone $el;
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($el, TRUE));
        $doc->formatOutput = TRUE;
        return $doc->saveXML();
    }
}
