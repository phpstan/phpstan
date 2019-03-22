<?php

namespace Levels\OffsetAccess;

function () {
	/** @var int|object $maybeInt */
	$maybeInt = null;

	$string = 'foo';
	echo $string[0];

	$string = 'foo';
	echo $string['foo'];

	$string = 'foo';
	echo $string[12.34];

	$string = 'foo';
	echo $string[$maybeInt];

	/** @var string|array $maybeString */
	$maybeString = [];
	echo $maybeString[0];

	/** @var string|array $maybeString */
	$maybeString = [];
	echo $maybeString['foo'];

	/** @var string|array $maybeString */
	$maybeString = [];
	echo $maybeString[12.34];

	/** @var string|array $maybeString */
	$maybeString = [];
	echo $maybeString[$maybeInt];

	/** @var mixed $mixed */
	$mixed = null;
	echo $mixed[0];

	/** @var mixed $mixed */
	$mixed = null;
	echo $mixed['foo'];

	/** @var mixed $mixed */
	$mixed = null;
	echo $mixed[12.34];

	/** @var mixed $mixed */
	$mixed = null;
	echo $mixed[$maybeInt];
};
