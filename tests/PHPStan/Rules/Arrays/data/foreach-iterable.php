<?php

foreach ([1, 2, 3] as $x) {

}

$string = 'foo';
foreach ($string as $x) {

}

$arrayOrFalse = [1, 2, 3];
if (doFoo()) {
	$arrayOrFalse = false;
}

foreach ($arrayOrFalse as $val) {

}
