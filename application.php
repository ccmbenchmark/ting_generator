<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use Symfony\Component\Console\Application;
use CCMBenchmark\TingGenerator\Command\TingGenerateCommand;

$application = new Application();
$application->add(new TingGenerateCommand());
$application->run();
