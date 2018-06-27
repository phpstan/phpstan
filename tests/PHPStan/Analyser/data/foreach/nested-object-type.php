<?php

use AnotherNamespace\Foo;

/** @var Foo[][] $fooses */
$fooses = foos();

foreach ($fooses as $foos) {
    foreach ($foos as $foo) {
        die;
    }
}
