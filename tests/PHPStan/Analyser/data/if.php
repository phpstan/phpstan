<?php

if (foo()) {
	$ifVar = 1;
	if ($test) {
		$ifNestedVar = 1;
		$ifNotNestedVar = 1;
	} elseif (fooBar()) {
		$ifNotNestedVar = 2;
		throw $e;
	} else {
		$ifNestedVar = 2;
	}
	$ifNotVar = 1;
} elseif (bar()) {
	$ifVar = 2;
	$ifNestedVar = 2;
	$ifNotNestedVar = 2;
	$ifNotVar = 2;
} elseif ($ifNestedVar = baz()) {
	$ifVar = 3;
	$ifNotNestedVar = 3;
} else {
	return;
}

try {
	$inTry = 1;
	$inTryNotInCatch = 1;
	$fooObjectFromTryCatch = new InTryCatchFoo();
	$mixedVarFromTryCatch = 1;
	$nullableIntegerFromTryCatch = 1;
	$anotherNullableIntegerFromTryCatch = null;
} catch (\SomeConcreteException $e) {
	$inTry = 1;
	$fooObjectFromTryCatch = new InTryCatchFoo();
	$mixedVarFromTryCatch = 1.0;
	$nullableIntegerFromTryCatch = null;
	$anotherNullableIntegerFromTryCatch = 1;
} catch (\Exception $e) {
	throw $e;
} finally {
	restore_error_handler();
}

$lorem = 1;
$arrOne[] = 'one';
$arrTwo['test'] = 'two';
$anotherArray['test'][] = 'another';
doSomething($one, $callParameter = 3);
$arrTwo[] = new Foo([
	$inArray = 1,
]);
preg_match('#.*#', 'foo', $matches);
if ((bool) preg_match('#.*#', 'foo', $matches3)) {
	foo();
} elseif (preg_match('#.*#', 'foo', $matches4)) {
	foo();
}
$someArray = [];
list($listedOne, , $listedTwo['two'], list($listedThree, $listedFour['four'])) = $someArray;

switch (foo()) {
	case 1:
		$switchVar = 1;
		$noSwitchVar = 1;
		break;
	case 2:
		$switchVar = 2;
		break;
	case 3:
	default:
		$switchVar = 3;
}

do {
	$doWhileVar = 1;
} while (something());

for ($previousI = 0, $previousJ = 0; $previousI < 1; $previousI++) {

}

while (($frame = $that->getReader()->consumeFrame($that->getReadBuffer())) === null) {

}

$nullableIntegers = [1, 2, 3];
$nullableIntegers[] = null;

$mixeds = [1, 2, 3];
$mixeds[] = 'foo';

$$lorem = 'ipsum';

$trueOrFalse = true;
$falseOrTrue = false;
$true = true;
$false = false;
if (doFoo()) {
	$trueOrFalse = false;
	$falseOrTrue = true;
	$true = true;
	$false = false;
}

try {
	$inTryTwo = 1;
} catch (\Exception $e) {
	$exception = $e;
	if (something()) {
		bar();
	} elseif (foo() || $foo = exists() || preg_match('#.*#', $subject, $matches2) || isset($issetBar)) {
		for ($i = 0; $i < 5; $i++, $f = $i) {
			foreach ($arr as list($listOne, $listTwo)) {
				(bool) preg_match('~.*~', $attributes, $ternaryMatches) ? die : null;
			}
		}
	}
}
