<?php

$x = null;

/** @var string[] $arr */
$arr = doFoo();
foreach ($arr as $foo) {
	$x = $foo;
}

die;
