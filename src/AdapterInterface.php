<?php
namespace FRNApp;

interface AdapterInterface {
    public function __construct($opts);
    public function getBroadcasts($ids = NULL);
}