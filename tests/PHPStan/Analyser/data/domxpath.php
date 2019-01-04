<?php

$doc = new DOMDocument();
$doc->loadXML('<test/>');
$xpath = new DOMXPath($doc);

$string1 = '"string1"';
$string2 = '"string2"';
$string3 = random_bytes(2);
$string1or2 = random_int(0, 1) ? $string1 : $string2;
$string1or3 = random_int(0, 1) ? $string1 : $string3;

$selector1 = '/selector1';
$selector2 = '/selector2';
$selector1or2 = random_int(0, 1) ? $selector1 : $selector2;

/** @var mixed $mixed */
$mixed = doFoo();

$string1orSelector1 = random_int(0, 1) ? $string1 : $selector1;

$string1orFalse = random_int(0, 1) ? $string1 : false;
$selector1orFalse = random_int(0, 1) ? $selector1 : false;

$string1orMixed = random_int(0, 1) ? $string1 : $mixed;
$selector1orMixed = random_int(0, 1) ? $string1 : $mixed;

die;
