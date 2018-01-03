#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use FRNApp\Command\GenerateDrupal;
use FRNApp\Command\Validate;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$application = new Application();


$application->add(new GenerateDrupal());
$application->add(new Validate());

$application->run();
