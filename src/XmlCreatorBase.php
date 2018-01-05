<?php

namespace FRNApp;

use Symfony\Component\Console\Exception\RuntimeException;

abstract class XmlCreatorBase implements XmlCreatorInterface
{
    /** @var AdapterInterface */
    protected $adapter;
    protected $opts;
    protected $idLimit;

    /** @var  \DOMDocument */
    public $doc;

    /** @var  array */
    protected $shows;

    /** @var \DOMElement */
    protected $info;

    /** @var \DOMElement */
    protected $channels;

    /** @var \DOMElement */
    protected $station;

    /** @var \DOMElement */
    protected $programme;

    public function __construct(AdapterInterface $adapter, $opts = array()) {
        $this->adapter = $adapter;
        $this->opts = $opts;
        $this->idLimit = [];
    }

    public function setIdLimit($ids) {
        if (empty($ids)) {
            return;
        }
        else if (!is_array($ids)) {
            if (strpos($ids, ',')) {
                $ids = explode(',', $ids);
            }
            else {
                $ids = [$ids];
            }
            // Convert to ints.
            $ids = array_filter(array_map(function($id) { return (int) $id; }, $ids), function($id) { return $id; } );
        }
        $this->idLimit = $ids;
    }

    public function createAndSaveXml($save_path) {
        $xml = $this->createXml();
        $ret = file_put_contents($save_path, $xml);
        return (bool) $ret;
    }

    public function createXml() {
        $doc = $this->createDomDocument();
        $doc->formatOutput = TRUE;
        $xml = $doc->saveXML();
        return $xml;
    }

    public function createDomDocument() {
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

        $this->shows = $this->adapter->getBroadcasts($this->idLimit);
        $id = 1;
        foreach ($this->shows as $show) {
            $broadcast = $this->getBroadcast($id, $show);
            if (!empty($broadcast)) {
                $this->programme->appendChild($broadcast);
                $id++;
            }
        }
        return $doc;
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
