<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

class FooPsalmPrefix extends FooParent
{

	/**
	 * @psalm-return Bar
	 */
	public static function doSomethingStatic()
	{

	}

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
	 * @psalm-param self $selfType
	 * @psalm-param static $staticType
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
	 * @psalm-param object $objectWithoutNativeTypehint
	 * @psalm-param object $objectWithNativeTypehint
	 * @psalm-return Foo
	 */
	public function doFoo(
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
		$selfType,
		$staticType,
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
		$parameterWithDefaultValueFalse = false,
		$objectWithoutNativeTypehint,
		object $objectWithNativeTypehint
	)
	{
		$parent = new FooParent();
		$differentInstance = new self();

		/** @var self $inlineSelf */
		$inlineSelf = doFoo();

		/** @var Bar $inlineBar */
		$inlineBar = doFoo();

		foreach ($moreSpecifiedObject->doFluentUnionIterable() as $fluentUnionIterableBaz) {
			die;
		}
	}

	/**
	 * @psalm-return self[]
	 */
	public function doBar(): array
	{

	}

	public function returnParent(): parent
	{

	}

	/**
	 * @psalm-return parent
	 */
	public function returnPhpDocParent()
	{

	}

	/**
	 * @psalm-return NULL[]
	 */
	public function returnNulls(): array
	{

	}

	public function returnObject(): object
	{

	}

	public function phpDocVoidMethod(): self
	{

	}

	public function phpDocVoidMethodFromInterface(): self
	{

	}

	public function phpDocVoidParentMethod(): self
	{

	}

	public function phpDocWithoutCurlyBracesVoidParentMethod(): self
	{

	}

	/**
	 * @psalm-return string[]
	 */
	public function returnsStringArray(): array
	{

	}

	private function privateMethodWithPhpDoc()
	{

	}

}
