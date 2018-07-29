<?php

$array = [];

$array[] = 10;
$array[][] = 10;
$array[];
var_dump($array[]);
while ($foo = $array[]) {}

$array['foo']['bar'];
$array['foo'][];
$array[]['bar'];
$array[][];

$firstElement = &$array[];
(function ($ref) {})($array[]);
//(function (&$ref) {})($array[]); // Should work but doesn't

// Technically works but makes no sense
$array[] += 20;
$array[] -= 20;
$array[] *= 20;
$array[] /= 20;

// NodeScopeResolver exceptions
isset($array[]);
unset($array[]);
empty($array[]);
$foo[] ?? $foo;
//global $array[]; // Parse error
//static $array[]; // Parse error
//function () use ($array[]) {} // Parse error
