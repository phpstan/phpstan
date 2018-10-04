<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\VerbosityLevel;

class AnnotationsMethodsClassReflectionExtensionTest extends \PHPStan\Testing\TestCase
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'passedByReference' => PassedByReference::createNo(),
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBar' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo',
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'passedByReference' => PassedByReference::createNo(),
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'static(AnnotationsMethods\Foo)',
				'isStatic' => false,
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'passedByReference' => PassedByReference::createNo(),
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo',
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'int',
						'passedByReference' => PassedByReference::createNo(),
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'b',
						'type' => 'mixed',
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'getFooOrBarStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'static(AnnotationsMethods\Foo)',
				'isStatic' => false,
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
				'returnType' => 'AnnotationsMethods\Foo',
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
				'returnType' => 'AnnotationsMethods\Foo',
				'isStatic' => true,
				'isVariadic' => false,
				'parameters' => [],
			],
			'methodWithNoReturnTypeStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'static(AnnotationsMethods\Foo)',
				'isStatic' => false,
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
				'returnType' => 'AnnotationsMethods\Foo',
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
				'returnType' => 'AnnotationsMethods\Foo',
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
						'type' => 'array',
						'passedByReference' => PassedByReference::createNo(),
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
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
					[
						'name' => 'backgroundColor',
						'type' => 'mixed',
						'passedByReference' => PassedByReference::createNo(),
						'isOptional' => false,
						'isVariadic' => false,
					],
				],
			],
			'overridenMethod' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => \AnnotationsMethods\Foo::class,
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
			'overridenMethodWithAnnotation' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => \AnnotationsMethods\Foo::class,
				'isStatic' => false,
				'isVariadic' => false,
				'parameters' => [],
			],
		];
		$barMethods = array_merge(
			$fooMethods,
			[
				'overridenMethod' => [
					'class' => \AnnotationsMethods\Bar::class,
					'returnType' => \AnnotationsMethods\Bar::class,
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'overridenMethodWithAnnotation' => [
					'class' => \AnnotationsMethods\Bar::class,
					'returnType' => \AnnotationsMethods\Bar::class,
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
				'conflictingMethod' => [
					'class' => \AnnotationsMethods\Bar::class,
					'returnType' => \AnnotationsMethods\Bar::class,
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [],
				],
			]
		);
		$bazMethods = array_merge(
			$barMethods,
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'passedByReference' => PassedByReference::createNo(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'int|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'int',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'int|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'int|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'int|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'int|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'OtherNamespace\Ipsum|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'OtherNamespace\Ipsum',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'OtherNamespace\Ipsum|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'OtherNamespace\Ipsum|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'OtherNamespace\Ipsum|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'OtherNamespace\Ipsum|null',
							'passedByReference' => PassedByReference::createCreatesNewVariable(),
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
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
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'b',
							'type' => 'mixed',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
						[
							'name' => 'c',
							'type' => 'bool|float|int|OtherNamespace\\Test|string',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'd',
							'type' => 'bool|float|int|OtherNamespace\\Test|string|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => true,
							'isVariadic' => false,
						],
					],
				],
				'paramMultipleTypesWithExtraSpaces' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'float|int',
					'isStatic' => false,
					'isVariadic' => false,
					'parameters' => [
						[
							'name' => 'string',
							'type' => 'string|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
							'isVariadic' => false,
						],
						[
							'name' => 'object',
							'type' => 'OtherNamespace\\Test|null',
							'passedByReference' => PassedByReference::createNo(),
							'isOptional' => false,
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
	 * @param array<string, mixed> $methods
	 */
	public function testMethods(string $className, array $methods): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(true);
		$scope->method('getClassReflection')->willReturn($class);
		$scope->method('canCallMethod')->willReturn(true);
		foreach ($methods as $methodName => $expectedMethodData) {
			$this->assertTrue($class->hasMethod($methodName), sprintf('Method %s() not found in class %s.', $methodName, $className));

			$method = $class->getMethod($methodName, $scope);
			$selectedParametersAcceptor = ParametersAcceptorSelector::selectSingle($method->getVariants());
			$this->assertSame(
				$expectedMethodData['class'],
				$method->getDeclaringClass()->getName(),
				sprintf('Declaring class of method %s() does not match.', $methodName)
			);
			$this->assertSame(
				$expectedMethodData['returnType'],
				$selectedParametersAcceptor->getReturnType()->describe(VerbosityLevel::precise()),
				sprintf('Return type of method %s::%s() does not match', $className, $methodName)
			);
			$this->assertSame(
				$expectedMethodData['isStatic'],
				$method->isStatic(),
				sprintf('Scope of method %s::%s() does not match', $className, $methodName)
			);
			$this->assertSame(
				$expectedMethodData['isVariadic'],
				$selectedParametersAcceptor->isVariadic(),
				sprintf('Method %s::%s() does not match expected variadicity', $className, $methodName)
			);
			$this->assertCount(
				count($expectedMethodData['parameters']),
				$selectedParametersAcceptor->getParameters(),
				sprintf('Method %s::%s() does not match expected count of parameters', $className, $methodName)
			);
			foreach ($selectedParametersAcceptor->getParameters() as $i => $parameter) {
				$this->assertSame(
					$expectedMethodData['parameters'][$i]['name'],
					$parameter->getName()
				);
				$this->assertSame(
					$expectedMethodData['parameters'][$i]['type'],
					$parameter->getType()->describe(VerbosityLevel::precise())
				);
				$this->assertTrue(
					$expectedMethodData['parameters'][$i]['passedByReference']->equals($parameter->passedByReference())
				);
				$this->assertSame(
					$expectedMethodData['parameters'][$i]['isOptional'],
					$parameter->isOptional()
				);
				$this->assertSame(
					$expectedMethodData['parameters'][$i]['isVariadic'],
					$parameter->isVariadic()
				);
			}
		}
	}

	public function testOverridingNativeMethodsWithAnnotationsDoesNotBreakGetNativeMethod(): void
	{
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass(\AnnotationsMethods\Bar::class);
		$this->assertTrue($class->hasNativeMethod('overridenMethodWithAnnotation'));
		$this->assertInstanceOf(PhpMethodReflection::class, $class->getNativeMethod('overridenMethodWithAnnotation'));
	}

}
