<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

class FooInheritDocChild extends Foo
{

	/**
	 * {@inheritdoc}
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
		$differentInstance = new Foo();

		/** @var self $inlineSelf */
		$inlineSelf = doFoo();

		/** @var Bar $inlineBar */
		$inlineBar = doFoo();
		foreach ($moreSpecifiedObject->doFluentUnionIterable() as $fluentUnionIterableBaz) {
			die;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	private function privateMethodWithPhpDoc()
	{

	}

}
