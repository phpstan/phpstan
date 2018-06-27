<?php

$x = null;

/** @var string[] $arr */
$arr = doFoo();
foreach ($arr as $foo) {
    $x = $foo;
}

$y = null;
if (doFoo()) {
} else {
    if (doBar()) {
    } else {
        $y = 1;
    }
}

die;
