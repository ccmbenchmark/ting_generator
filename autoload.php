<?php

spl_autoload_register(function ($class) {

    $filename = str_replace('\\' , '/', $class);
    $filename = str_replace('CCMBenchmark', 'src', $filename);

    require_once $filename . '.php';
});
