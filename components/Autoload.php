<?php

spl_autoload_register(function ($class_name)
{
    $array_path = [
        '/models/',
        '/components/'
    ];

    foreach ($array_path as $path) {
        $path = ROOT . $path . $class_name . '.php';
        if (is_file($path)) {
            require_once($path);
        }
    }
});