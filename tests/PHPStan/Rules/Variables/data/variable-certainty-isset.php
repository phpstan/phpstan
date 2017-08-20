<?php

/** @var string|null $alwaysDefinedNullable */
$alwaysDefinedNullable = doFoo();

if (isset($alwaysDefinedNullable)) { // fine, checking for nullability

}

$alwaysDefinedNotNullable = 'string';
if (isset($alwaysDefinedNotNullable)) { // always true

}

if (doFoo()) {
	$sometimesDefinedVariable = 1;
}

if (isset(
	$sometimesDefinedVariable, // fine, this is what's isset() is for
	$neverDefinedVariable // always false
)) {

}

/** @var string|null $anotherAlwaysDefinedNullable */
$anotherAlwaysDefinedNullable = doFoo();

if (isset($anotherAlwaysDefinedNullable['test']['test'])) { // fine, checking for nullability

}

$anotherAlwaysDefinedNotNullable = 'string';

if (isset($anotherAlwaysDefinedNotNullable['test']['test'])) { // fine, variable always exists, but what about the array index?

}

if (isset($anotherNeverDefinedVariable['test']['test']->test['test']['test'])) { // always false

}
