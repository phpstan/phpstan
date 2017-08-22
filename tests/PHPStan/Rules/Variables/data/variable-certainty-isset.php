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

if (isset($yetAnotherNeverDefinedVariable::$test['test'])) { // always false

}

if (isset($_COOKIE['test'])) { // fine

}

if (something()) {

} elseif (isset($yetYetAnotherNeverDefinedVariableInIsset)) { // always false

}

if (doFoo()) {
	$yetAnotherVariableThatSometimesExists = 1;
}

if (something()) {

} elseif (isset($yetAnotherVariableThatSometimesExists)) { // fine

}

/** @var string|null $nullableVariableUsedInTernary */
$nullableVariableUsedInTernary = doFoo();
echo isset($nullableVariableUsedInTernary) ? 'foo' : 'bar'; // fine

/** @var int|null $forVariableInit */
$forVariableInit = doFoo();

/** @var int|null $forVariableCond */
$forVariableCond = doFoo();

/** @var int|null $forVariableLoop */
$forVariableLoop = doFoo();

for ($i = 0, $init = isset($forVariableInit); $i < 10 && isset($forVariableCond); $i++, $loop = isset($forVariableLoop)) {

}

if (something()) {
	$variableInWhile = 1;
}

while (isset($variableInWhile)) {
	unset($variableInWhile);
}
