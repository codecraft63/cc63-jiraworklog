<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\GenerateReportCommand;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$application = new Application();

$application->add(new GenerateReportCommand());

$application->run();