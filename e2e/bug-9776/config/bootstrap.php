<?php

use CristianHG2\Backend\ServiceProvider;

require_once __DIR__.'/../vendor/autoload.php';

$providers = [
    ServiceProvider::class
];

foreach ($providers as $provider) {
    (new $provider())->boot();
}
