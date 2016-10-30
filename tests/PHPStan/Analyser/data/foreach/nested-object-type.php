<?php

use AnotherNamespace\Foo;

/** @var $fooses Foo[][] */
$fooses = foos();

foreach ($fooses as $foos) {
	foreach ($foos as $foo) {
		die;
	}
}
