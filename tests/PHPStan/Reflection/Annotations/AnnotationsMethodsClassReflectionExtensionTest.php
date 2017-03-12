<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Broker\Broker;

class AnnotationsMethodsClassReflectionExtensionTest extends \PHPStan\TestCase
{

	public function dataMethods(): array
	{
		return [
			[
				\AnnotationsMethods\Foo::class,
				[
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
				],
			],
			[
				\AnnotationsMethods\Bar::class,
				[
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
				],
			],
			[
				\AnnotationsMethods\Baz::class,
				[
					'getInteger' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'int',
						'isStatic' => false,
					],
					'doSomething' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => false,
					],
					'getFooOrBar' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => false,
					],
					'getIpsum' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
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
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => true,
					],
					'getFooOrBarStatically' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => true,
					],
					'getIpsumStatically' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
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
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => false,
					],
					'getFooOrBarWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => false,
					],
					'getIpsumWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
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
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => true,
					],
					'getFooOrBarStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => true,
					],
					'getIpsumStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
						'isStatic' => true,
					],
					'methodWithNoReturnTypeStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'mixed',
						'isStatic' => true,
					],
				],
			],
			[
				\AnnotationsMethods\BazBaz::class,
				[
					'getInteger' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'int',
						'isStatic' => false,
					],
					'doSomething' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => false,
					],
					'getFooOrBar' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => false,
					],
					'getIpsum' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
						'isStatic' => false,
					],
					'methodWithNoReturnType' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'mixed',
						'isStatic' => false,
					],
					'getTest' => [
						'class' => \AnnotationsMethods\BazBaz::class,
						'returnType' => 'OtherNamespace\Test',
						'isStatic' => false,
					],
					'getIntegerStatically' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'int',
						'isStatic' => true,
					],
					'doSomethingStatically' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => true,
					],
					'getFooOrBarStatically' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => true,
					],
					'getIpsumStatically' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
						'isStatic' => true,
					],
					'methodWithNoReturnTypeStatically' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'mixed',
						'isStatic' => true,
					],
					'getTestStatically' => [
						'class' => \AnnotationsMethods\BazBaz::class,
						'returnType' => 'OtherNamespace\Test',
						'isStatic' => true,
					],
					'getIntegerWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'int',
						'isStatic' => false,
					],
					'doSomethingWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => false,
					],
					'getFooOrBarWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => false,
					],
					'getIpsumWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
						'isStatic' => false,
					],
					'methodWithNoReturnTypeWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'mixed',
						'isStatic' => false,
					],
					'getTestWithDescription' => [
						'class' => \AnnotationsMethods\BazBaz::class,
						'returnType' => 'OtherNamespace\Test',
						'isStatic' => false,
					],
					'getIntegerStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'int',
						'isStatic' => true,
					],
					'doSomethingStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'void',
						'isStatic' => true,
					],
					'getFooOrBarStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
						'isStatic' => true,
					],
					'getIpsumStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Baz::class,
						'returnType' => 'OtherNamespace\Ipsum',
						'isStatic' => true,
					],
					'methodWithNoReturnTypeStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\Foo::class,
						'returnType' => 'mixed',
						'isStatic' => true,
					],
					'getTestStaticallyWithDescription' => [
						'class' => \AnnotationsMethods\BazBaz::class,
						'returnType' => 'OtherNamespace\Test',
						'isStatic' => true,
					],
				],
			],
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
