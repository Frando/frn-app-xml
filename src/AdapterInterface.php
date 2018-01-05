<?php
namespace FRNApp;

interface AdapterInterface {
    public function __construct($opts = array());

    /**
     * Get broadcasted shows.
     *
     * @param array|null $ids Array of IDs to limit output. NULL for no limit.
     * @return array Array of objects containing all broadcast information needed for XML creation.
     */
    public function getBroadcasts($ids = NULL);
}