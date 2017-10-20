<?php

spl_autoload_register(function ($class) {

    $filename = str_replace('\\' , '/', $class);

    require_once $filename . '.php';
});
