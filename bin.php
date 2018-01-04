#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use FRNApp\Command\GenerateDrupalRDL;
use FRNApp\Command\GenerateDrupalFreeFM;
use FRNApp\Command\Validate;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$application = new Application();


$application->add(new GenerateDrupalRDL());
$application->add(new GenerateDrupalFreeFM());
$application->add(new Validate());

$application->run();
