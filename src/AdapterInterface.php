<?php
namespace FRNApp;

interface AdapterInterface {
    public function __construct($opts = array());
    public function getBroadcasts($ids = NULL);
}