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
			],
			'doSomething' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
			],
			'getFooOrBar' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => false,
			],
			'methodWithNoReturnType' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
			],
			'getIntegerStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
			],
			'doSomethingStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
			],
			'getFooOrBarStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => true,
			],
			'methodWithNoReturnTypeStatically' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
			],
			'getIntegerWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
			],
			'doSomethingWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
			],
			'getFooOrBarWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => false,
			],
			'methodWithNoReturnTypeWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
			],
			'getIntegerStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
			],
			'doSomethingStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
			],
			'getFooOrBarStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => true,
			],
			'methodWithNoReturnTypeStaticallyWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClass' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'bool',
				'isStatic' => true,
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescription' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'string',
				'isStatic' => true,
			],
			'getIntegerNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
			],
			'doSomethingNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
			],
			'getFooOrBarNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => false,
			],
			'methodWithNoReturnTypeNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => false,
			],
			'getIntegerStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
			],
			'doSomethingStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
			],
			'getFooOrBarStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => true,
			],
			'methodWithNoReturnTypeStaticallyNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'mixed',
				'isStatic' => true,
			],
			'getIntegerWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => false,
			],
			'doSomethingWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => false,
			],
			'getFooOrBarWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => false,
			],
//					'methodWithNoReturnTypeWithDescriptionNoParams' => [
//						'class' => \AnnotationsMethods\Foo::class,
//						'returnType' => 'mixed',
//						'isStatic' => false,
//					],
			'getIntegerStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'int',
				'isStatic' => true,
			],
			'doSomethingStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'void',
				'isStatic' => true,
			],
			'getFooOrBarStaticallyWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
				'isStatic' => true,
			],
//					'methodWithNoReturnTypeStaticallyWithDescriptionNoParams' => [
//						'class' => \AnnotationsMethods\Foo::class,
//						'returnType' => 'mixed',
//						'isStatic' => true,
//					],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'bool|string',
				'isStatic' => true,
			],
			'aStaticMethodThatHasAUniqueReturnTypeInThisClassWithDescriptionNoParams' => [
				'class' => \AnnotationsMethods\Foo::class,
				'returnType' => 'string|float',
				'isStatic' => true,
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
				],
				'getIpsum' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => false,
				],
				'getIpsumStatically' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => true,
				],
				'getIpsumWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => false,
				],
				'getIpsumStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'OtherNamespace\Ipsum',
					'isStatic' => true,
				],
				'doSomethingStatically' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
				],
				'doSomethingWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
				],
				'doSomethingStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
				],
				'doSomethingNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
				],
				'doSomethingStaticallyNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
				],
				'doSomethingWithDescriptionNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => false,
				],
				'doSomethingStaticallyWithDescriptionNoParams' => [
					'class' => \AnnotationsMethods\Baz::class,
					'returnType' => 'void',
					'isStatic' => true,
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
				],
				'getTestStatically' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => true,
				],
				'getTestWithDescription' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => false,
				],
				'getTestStaticallyWithDescription' => [
					'class' => \AnnotationsMethods\BazBaz::class,
					'returnType' => 'OtherNamespace\Test',
					'isStatic' => true,
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

		}
	}

}
