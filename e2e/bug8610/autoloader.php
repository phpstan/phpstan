<?php

spl_autoload_register(function() {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/classes'));
    foreach ($iterator as $path => $file) {
        // ...
    }
});