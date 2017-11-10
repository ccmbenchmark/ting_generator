<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \CCMBenchmark\TingGenerator\Command\TingGenerateCommand());

$application->run();
