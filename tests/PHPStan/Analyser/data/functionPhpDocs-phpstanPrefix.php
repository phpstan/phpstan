<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

/**
 * @phpstan-param Foo|Bar $unionTypeParameter
 * @phpstan-param int $anotherMixedParameter
 * @phpstan-param int $anotherMixedParameter
 * @phpstan-paran int $yetAnotherMixedProperty
 * @phpstan-param int $integerParameter
 * @phpstan-param integer $anotherIntegerParameter
 * @phpstan-param aRray $arrayParameterOne
 * @phpstan-param mixed[] $arrayParameterOther
 * @phpstan-param Lorem $objectRelative
 * @phpstan-param \SomeOtherNamespace\Ipsum $objectFullyQualified
 * @phpstan-param Dolor $objectUsed
 * @phpstan-param null|int $nullableInteger
 * @phpstan-param Dolor|null $nullableObject
 * @phpstan-param Dolor $anotherNullableObject
 * @phpstan-param Null $nullType
 * @phpstan-param Bar $barObject
 * @phpstan-param Foo $conflictedObject
 * @phpstan-param Baz $moreSpecifiedObject
 * @phpstan-param resource $resource
 * @phpstan-param array[array] $yetAnotherAnotherMixedParameter
 * @phpstan-param \\Test\Bar $yetAnotherAnotherAnotherMixedParameter
 * @phpstan-param New $yetAnotherAnotherAnotherAnotherMixedParameter
 * @phpstan-param void $voidParameter
 * @phpstan-param Consecteur $useWithoutAlias
 * @phpstan-param true $true
 * @phpstan-param false $false
 * @phpstan-param true $boolTrue
 * @phpstan-param false $boolFalse
 * @phpstan-param bool $trueBoolean
 * @phpstan-param bool $parameterWithDefaultValueFalse
 * @phpstan-return Foo
 */
function doFooPhpstanPrefix(
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
