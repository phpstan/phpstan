<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Broker\Broker;

class AnnotationsMethodsClassReflectionExtensionTest extends \PHPStan\TestCase
{

	public function dataMethods(): array
	{
		$fooMethods = [
			'getInteger' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'doSomething' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBar' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnType' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'doSomethingStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'doSomethingWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'doSomethingStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'a',
						'type' => 'int',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClass' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'bool',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'string',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'doSomethingNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getFooOrBarNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'doSomethingStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getFooOrBarStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'doSomethingWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getFooOrBarWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getIntegerStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'doSomethingStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'getFooOrBarStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Bar|AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'bool|string',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'float|string',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodFromInterface' => [
				'class' => \AnnotationsMethods\FooInterface::class,
				'returnType' => \AnnotationsMethods\FooInterface::class,
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'publish' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'Aws\Result',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'args',
						'type' => 'mixed[]',
						'isPassedByReference' => false,
						'isOptional' => true,
						'isVariadic' => false,
					],
				],
			],
			'rotate' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Image',
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [
					[
						'name' => 'angle',
						'type' => 'float',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'backgroundColor',
						'type' => 'mixed',
						'isPassedByReference' => false,
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
		];
		$barMethods = $fooMethods;
		$bazMethods = array_merge(
			$fooMethods,
			[
				'doSomething' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'getIpsum' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'getIpsumStatically' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'getIpsumWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'getIpsumStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingStatically' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'doSomethingStaticallyNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [],
				],
				'doSomethingWithDescriptionNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'doSomethingStaticallyWithDescriptionNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [],
				],
				'methodFromTrait' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => \AnnotationsMethods\BazBaz::class,
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
			]
		);
		$bazBazMethods = array_merge(
			$bazMethods,
			[
				'getTest' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'getTestStatically' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [],
				],
				'getTestWithDescription' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'getTestStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => true,
					'isVariadic' => false,
					'parameters' => [],
				],
				'doSomethingWithSpecificScalarParamsWithoutDefault' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'int|null',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'int',
							'isPassedByReference' => true,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'int|null',
							'isPassedByReference' => true,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingWithSpecificScalarParamsWithDefault' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int|null',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'int|null',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'int|null',
							'isPassedByReference' => true,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'int|null',
							'isPassedByReference' => true,
							'isOptional' => true,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingWithSpecificObjectParamsWithoutDefault' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'OtherNamespace\Ipsum',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'OtherNamespace\Ipsum',
							'isPassedByReference' => true,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => true,
							'isOptional' => false,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingWithSpecificObjectParamsWithDefault' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => true,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => true,
							'isOptional' => true,
							'isVariadic' => false,
						],
					],
				],
				'doSomethingWithSpecificVariadicScalarParamsNotNullable' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => true,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => true,
						],
					],
				],
				'doSomethingWithSpecificVariadicScalarParamsNullable' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => true,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'int|null',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => true,
						],
					],
				],
				'doSomethingWithSpecificVariadicObjectParamsNotNullable' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => true,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'OtherNamespace\Ipsum',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => true,
						],
					],
				],
				'doSomethingWithSpecificVariadicObjectParamsNullable' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => true,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'OtherNamespace\Ipsum|null',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => true,
						],
					],
				],
				'doSomethingWithComplicatedParameters' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'void',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'a',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'bool|float|int|OtherNamespace\\Test|string',
							'isPassedByReference' => false,
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'bool|float|int|OtherNamespace\\Test|string|null',
							'isPassedByReference' => false,
							'isOptional' => true,
							'isVariadic' => false,
						],
					],
				],
			]
		);

		return [
			[\AnnotationsMethods\Foo::class, $fooMethods],
			[\AnnotationsMethods\Bar::class, $barMethods],
			[\AnnotationsMethods\Baz::class, $bazMethods],
			[\AnnotationsMethods\BazBaz::class, $bazBazMethods],
		];
	}

	/**
	 * @dataProvider dataMethods
	 * @param string $className
	 * @param mixed[] $methods
	 */
	public function testMethods(string $className, array $methods)
	{
		/** @var Broker $broker */
		$broker = $this->getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		foreach ($methods as $methodName => $expectedMethodData) {
			$this->assertTrue($class->hasMethod($methodName), sprintf('Method %s not found in class %s.', $methodName, $className));

			$method = $class->getMethod($methodName);
			$this->assertSame(
				$expectedMethodData['class'],
				$method->getDeclaringClass()->getName(),
				sprintf('Declaring class of method $%s does not match.', $methodName)
			);
			$this->assertEquals(
				$expectedMethodData['returnType'],
				$method->getReturnType()->describe(),
				sprintf('Return type of method %s::%s does not match', $className, $methodName)
			);
			$this->assertEquals(
				$expectedMethodData['isStatic'],
				$method->isStatic(),
				sprintf('Scope of method %s::%s does not match', $className, $methodName)
			);
			$this->assertEquals(
				$expectedMethodData['isVariadic'],
				$method->isVariadic(),
				sprintf('Method %s::%s does not match expected variadicity', $className, $methodName)
			);
			$this->assertCount(
				count($expectedMethodData['parameters']),
				$method->getParameters(),
				sprintf('Method %s::%s does not match expected count of parameters', $className, $methodName)
			);
			foreach ($method->getParameters() as $i => $parameter) {
				$this->assertEquals(
					$expectedMethodData['parameters'][$i]['name'],
					$parameter->getName()
				);
				$this->assertEquals(
					$expectedMethodData['parameters'][$i]['type'],
					$parameter->getType()->describe()
				);
				$this->assertEquals(
					$expectedMethodData['parameters'][$i]['isPassedByReference'],
					$parameter->isPassedByReference()
				);
				$this->assertEquals(
					$expectedMethodData['parameters'][$i]['isOptional'],
					$parameter->isOptional()
				);
				$this->assertEquals(
					$expectedMethodData['parameters'][$i]['isVariadic'],
					$parameter->isVariadic()
				);
			}
		}
	}

}
