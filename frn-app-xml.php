#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

define('FRNAPP_DIR', dirname(__FILE__));

use FRNApp\Command\Validate;
use FRNApp\FreeFm\FreeFMCommand;
use FRNApp\Rdl\RdlCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load(__DIR__ . '/.env');
}

$application = new Application();

$application->add(new RdlCommand());
$application->add(new FreeFMCommand());
$application->add(new Validate());

$application->run();
