<?php

namespace ConstantTypesIncrementDecrement;

$postIncrement = 1;
$postIncrement++;

$preIncrement = 1;
++$preIncrement;

$postDecrement = 5;
$postDecrement--;

$preDecrement = 5;
--$preDecrement;

$literalArray = [
	'a' => 1,
	'b' => 5,
	'c' => 1,
	'd' => 5,
];
$literalArray['a']++;
$literalArray['b']--;
++$literalArray['c'];
--$literalArray['d'];

$nullIncremented = null;
$nullDecremented = null;
$nullIncremented++;
$nullDecremented--;

die;
