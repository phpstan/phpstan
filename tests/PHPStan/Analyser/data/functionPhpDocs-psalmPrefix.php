<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

/**
 * @psalm-param Foo|Bar $unionTypeParameter
 * @psalm-param int $anotherMixedParameter
 * @psalm-param int $anotherMixedParameter
 * @psalm-paran int $yetAnotherMixedProperty
 * @psalm-param int $integerParameter
 * @psalm-param integer $anotherIntegerParameter
 * @psalm-param aRray $arrayParameterOne
 * @psalm-param mixed[] $arrayParameterOther
 * @psalm-param Lorem $objectRelative
 * @psalm-param \SomeOtherNamespace\Ipsum $objectFullyQualified
 * @psalm-param Dolor $objectUsed
 * @psalm-param null|int $nullableInteger
 * @psalm-param Dolor|null $nullableObject
 * @psalm-param Dolor $anotherNullableObject
 * @psalm-param Null $nullType
 * @psalm-param Bar $barObject
 * @psalm-param Foo $conflictedObject
 * @psalm-param Baz $moreSpecifiedObject
 * @psalm-param resource $resource
 * @psalm-param array[array] $yetAnotherAnotherMixedParameter
 * @psalm-param \\Test\Bar $yetAnotherAnotherAnotherMixedParameter
 * @psalm-param New $yetAnotherAnotherAnotherAnotherMixedParameter
 * @psalm-param void $voidParameter
 * @psalm-param Consecteur $useWithoutAlias
 * @psalm-param true $true
 * @psalm-param false $false
 * @psalm-param true $boolTrue
 * @psalm-param false $boolFalse
 * @psalm-param bool $trueBoolean
 * @psalm-param bool $parameterWithDefaultValueFalse
 * @psalm-return Foo
 */
function doFooPsalmPrefix(
	$mixedParameter,
	$unionTypeParameter,
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
	$useWithoutAlias,
	$true,
	$false,
	bool $boolTrue,
	bool $boolFalse,
	bool $trueBoolean,
	$parameterWithDefaultValueFalse = false
)
{
	$fooFunctionResult = doFoo();

	foreach ($moreSpecifiedObject->doFluentUnionIterable() as $fluentUnionIterableBaz) {
		die;
	}
}
