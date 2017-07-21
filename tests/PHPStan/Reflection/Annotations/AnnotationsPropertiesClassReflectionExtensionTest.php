<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Broker\Broker;

class AnnotationsPropertiesClassReflectionExtensionTest extends \PHPStan\TestCase
{

	public function dataProperties(): array
	{
		return [
			[
				\AnnotationsProperties\Foo::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
						'writable' => true,
						'readable' => true,
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => false,
						'readable' => true,
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Bar|AnnotationsProperties\Foo',
						'writable' => true,
						'readable' => true,
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => true,
						'readable' => true,
					],
					'interfaceProperty' => [
						'class' => \AnnotationsProperties\FooInterface::class,
						'type' => \AnnotationsProperties\FooInterface::class,
						'writable' => true,
						'readable' => true,
					],
				],
			],
			[
				\AnnotationsProperties\Bar::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
						'writable' => true,
						'readable' => true,
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => false,
						'readable' => true,
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Bar|AnnotationsProperties\Foo',
						'writable' => true,
						'readable' => true,
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => true,
						'readable' => true,
					],
				],
			],
			[
				\AnnotationsProperties\Baz::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
						'writable' => true,
						'readable' => true,
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => false,
						'readable' => true,
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Bar|AnnotationsProperties\Foo',
						'writable' => true,
						'readable' => true,
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Dolor',
						'writable' => true,
						'readable' => true,
					],
					'bazProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem',
						'writable' => true,
						'readable' => true,
					],
					'traitProperty' => [
						'class' => \AnnotationsProperties\FooTrait::class,
						'type' => 'AnnotationsProperties\BazBaz',
						'writable' => true,
						'readable' => true,
					],
					'writeOnlyProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem|null',
						'writable' => true,
						'readable' => false,
					],
				],
			],
			[
				\AnnotationsProperties\BazBaz::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
						'writable' => true,
						'readable' => true,
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
						'writable' => false,
						'readable' => true,
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Bar|AnnotationsProperties\Foo',
						'writable' => true,
						'readable' => true,
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Dolor',
						'writable' => true,
						'readable' => true,
					],
					'bazProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem',
						'writable' => true,
						'readable' => true,
					],
					'traitProperty' => [
						'class' => \AnnotationsProperties\FooTrait::class,
						'type' => 'AnnotationsProperties\BazBaz',
						'writable' => true,
						'readable' => true,
					],
					'writeOnlyProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem|null',
						'writable' => true,
						'readable' => false,
					],
					'numericBazBazProperty' => [
						'class' => \AnnotationsProperties\BazBaz::class,
						'type' => 'float|int',
						'writable' => true,
						'readable' => true,
					],
				],
			],
		];
	}

	/**
	 * @dataProvider dataProperties
	 * @param string $className
	 * @param mixed[] $properties
	 */
	public function testProperties(string $className, array $properties)
	{
		/** @var Broker $broker */
		$broker = $this->getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		foreach ($properties as $propertyName => $expectedPropertyData) {
			$this->assertTrue(
				$class->hasProperty($propertyName),
				sprintf('Class %s does not define property %s.', $className, $propertyName)
			);

			$property = $class->getProperty($propertyName);
			$this->assertSame(
				$expectedPropertyData['class'],
				$property->getDeclaringClass()->getName(),
				sprintf('Declaring class of property $%s does not match.', $propertyName)
			);
			$this->assertSame(
				$expectedPropertyData['type'],
				$property->getType()->describe(),
				sprintf('Type of property %s::$%s does not match.', $property->getDeclaringClass()->getName(), $propertyName)
			);
			$this->assertSame(
				$expectedPropertyData['readable'],
				$property->isReadable(),
				sprintf('Property %s::$%s readability is not as expected.', $property->getDeclaringClass()->getName(), $propertyName)
			);
			$this->assertSame(
				$expectedPropertyData['writable'],
				$property->isWritable(),
				sprintf('Property %s::$%s writability is not as expected.', $property->getDeclaringClass()->getName(), $propertyName)
			);
		}
	}

}
