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

die;
