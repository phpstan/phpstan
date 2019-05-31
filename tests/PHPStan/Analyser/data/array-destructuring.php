<?php

/** @var mixed $array */
$array = getMixed();
[$a, $b, [$c]] = $array;
list($aList, $bList, list($cList)) = $array;

$constantArray = [1, 'foo', [true]];
[$int, $string, [$bool, $nestedNever], $never] = $constantArray;
list($intList, $stringList, list($boolList, $nestedNeverList), $neverList) = $constantArray;

$unionArray = $foo ? [1, 2, 3] : [4, 'bar'];
[$u1, $u2, $u3] = $unionArray;

foreach ([[1, [false]]] as [$foreachInt, [$foreachBool, $foreachNestedNever], $foreachNever]) {

}

foreach ([[1, [false]]] as list($foreachIntList, list($foreachBoolList, $foreachNestedNeverList), $foreachNeverList)) {

}

foreach ([$unionArray] as [$foreachU1, $foreachU2, $foreachU3]) {

}

/** @var string[] $stringArray */
$stringArray = getStringArray();
[$firstStringArray, $secondStringArray, [$thirdStringArray], $fourthStringArray] = $stringArray;
list($firstStringArrayList, $secondStringArrayList, list($thirdStringArrayList), $fourthStringArrayList) = $stringArray;

foreach ($stringArray as [$firstStringArrayForeach, $secondStringArrayForeach, [$thirdStringArrayForeach], $fourthStringArrayForeach]) {

}

foreach ($stringArray as list($firstStringArrayForeachList, $secondStringArrayForeachList, list($thirdStringArrayForeachList), $fourthStringArrayForeachList)) {

}

/** @var int $dayInt */
$dayInt = getInt($dayInt);
$dateArray = ['d' => $dayInt];
[$dateArray['Y'], $dateArray['m']] = explode('-', '2018-12-19');

/** @var int $firstIntElement */
$firstIntElement = getInt();
/** @var int $secondIntElement */
$secondIntElement = getInt();
$intArrayForRewritingFirstElement = [$firstIntElement, $secondIntElement];
[$intArrayForRewritingFirstElement[0]] = explode('*', '');

[$newArray['newKey']] = [new stdClass(), new stdClass()];

$obj = new stdClass();
[$obj[0]] = ['error', 'error-error'];

$constantAssocArray = [1, 'foo', 'key' => true, 'value' => '123'];
['key' => $assocKey, 0 => $assocOne, 1 => $assocFoo, 'non-existent' => $assocNonExistent] = $constantAssocArray;

$fooKey = 'key';
/** @var string $stringKey */
$stringKey = getString();
/** @var mixed $mixedKey */
$mixedKey = getMixed();
[$fooKey => $dynamicAssocKey, $stringKey => $dynamicAssocStrings, $mixedKey => $dynamicAssocMixed] = $constantAssocArray;

foreach ([$constantAssocArray] as [$fooKey => $dynamicAssocKeyForeach, $stringKey => $dynamicAssocStringsForeach, $mixedKey => $dynamicAssocMixedForeach]) {

}

/** @var iterable<array<string>> $iterableOverStringArrays */
$iterableOverStringArrays = doFoo();
foreach ($iterableOverStringArrays as [$stringFromIterable]) {

}

die;
