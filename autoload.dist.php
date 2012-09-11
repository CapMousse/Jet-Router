<?php

spl_autoload_register(function ($className){
    $className = ltrim($className, '\\');
    $fileName  = __DIR__.'/lib/';
    $namespace = '';

    if(strchr($className, 'Test') !== false){
        $fileName  = __DIR__.'/';
    }

    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    var_dump($fileName);

    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    include $fileName;
});