<?php

namespace SwitchFallThrough;

interface A {}
interface B {}
interface C {}
interface D {}

/** @var A|B|C|D $expr */
$expr = time();
$case1 = false;
$case2 = false;
$case3 = false;
$default = false;

switch (true) {
	case $expr instanceof A:
		$case1 = true;
		'1stCaseBranch'; // expr is A
	case $expr instanceof B:
		$case2 = true;
		'2ndCaseBranch'; // expr is A|B
	default:
		$default = true;
		'defaultBranch'; // expr is A|B|D
	case $expr instanceof C:
		$case3 = true;
		'3rdCaseBranch'; // expr is A|B|C|D
}

'afterSwitch';
