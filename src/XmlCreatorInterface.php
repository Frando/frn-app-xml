<?php
namespace FRNApp;

interface XmlCreatorInterface
{
    public function __construct(AdapterInterface $adapter, $opts = array());

    /**
     * @param string $save_path
     * @return void
     */
    public function createAndSaveXml($save_path);

    /**
     * @return string XML
     */
    public function createXml();

    /**
     * @return \DOMDocument Dom Document
     */
    public function createDomDocument();

    /**
     * @param array|string|int $ids Single ID, comma seperated list of IDs or array of IDs to limit output.
     * @return void
     */
    public function setIdLimit($ids);

    /**
     * @param int $id Rolling ID (include as attribute on the returned \DOMElement.
     * @param object $info Broadcast object (as returned from the adapter).
     * @return \DOMElement
     */
    public function getBroadcast($id, $info);

    /**
     * @return \DOMElement
     */
    public function getMediaChannels();

    /**
     * @return \DOMElement
     */
    public function getInfo();
}