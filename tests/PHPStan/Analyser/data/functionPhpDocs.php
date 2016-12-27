<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

/**
 * @param Foo|Bar $alsoMixedParameter
 * @param int $anotherMixedParameter
 * @param int $anotherMixedParameter
 * @paran int $yetAnotherMixedProperty
 * @param int $integerParameter
 * @param integer $anotherIntegerParameter
 * @param aRray $arrayParameterOne
 * @param mixed[] $arrayParameterOther
 * @param Lorem $objectRelative
 * @param \SomeOtherNamespace\Ipsum $objectFullyQualified
 * @param Dolor $objectUsed
 * @param null|int $nullableInteger
 * @param Dolor|null $nullableObject
 * @param Dolor $anotherNullableObject
 * @param Null $nullType
 * @param Bar $barObject
 * @param Foo $conflictedObject
 * @param Baz $moreSpecifiedObject
 * @param resource $resource
 * @param array[array] $yetAnotherAnotherMixedParameter
 * @param \\Test\Bar $yetAnotherAnotherAnotherMixedParameter
 * @param New $yetAnotherAnotherAnotherAnotherMixedParameter
 * @param void $voidParameter
 * @param Consecteur $useWithoutAlias
 * @return Foo
 */
function doFoo(
	$mixedParameter,
	$alsoMixedParameter,
	$anotherMixedParameter,
	$yetAnotherMixedParameter,
	$integerParameter,
	$anotherIntegerParameter,
	$arrayParameterOne,
	$arrayParameterOther,
	$objectRelative,
	$objectFullyQualified,
	$objectUsed,
	$nullableInteger,
	$nullableObject,
	$anotherNullableObject = null,
	$nullType,
	$barObject,
	Bar $conflictedObject,
	Bar $moreSpecifiedObject,
	$resource,
	$yetAnotherAnotherMixedParameter,
	$yetAnotherAnotherAnotherMixedParameter,
	$yetAnotherAnotherAnotherAnotherMixedParameter,
	$voidParameter,
	$useWithoutAlias
)
{
	$fooFunctionResult = doFoo();
	$barFunctionResult = doBar();

	foreach ($moreSpecifiedObject->doFluentUnionIterable() as $fluentUnionIterableBaz) {
		die;
	}
}

function doBar(): Bar
{

}
