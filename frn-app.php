#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use FRNApp\Command\Validate;

use FRNApp\Rdl\RdlCommand;
use FRNApp\FreeFm\FreeFMCommand;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$application = new Application();

$application->add(new RdlCommand());
$application->add(new FreeFMCommand());
$application->add(new Validate());

$application->run();
