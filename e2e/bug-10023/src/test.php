<?php

use Madbriller\PhpstanRepro\Consumer;
use Madbriller\PhpstanRepro\Impl;

require __DIR__ . '/../vendor/autoload.php';

$impl = new Impl();

$consumer = new Consumer();

$consumer->call($impl);

var_dump('reached');
