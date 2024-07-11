<?php
spl_autoload_register(function ($className) {
        $classPath = str_replace('\\', '/', $className);
        $file = __DIR__."/$classPath.php";
        // echo print_r($file).'<br>';
        if (file_exists($file)) {
            include_once $file;
        }
    });
?>