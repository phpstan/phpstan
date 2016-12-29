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
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Foo|AnnotationsProperties\Bar',
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
				],
			],
			[
				\AnnotationsProperties\Bar::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Foo|AnnotationsProperties\Bar',
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
				],
			],
			[
				\AnnotationsProperties\Baz::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Foo|AnnotationsProperties\Bar',
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Dolor',
					],
					'bazProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem',
					],
				],
			],
			[
				\AnnotationsProperties\BazBaz::class,
				[
					'otherTest' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Test',
					],
					'otherTestReadOnly' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'OtherNamespace\Ipsum',
					],
					'fooOrBar' => [
						'class' => \AnnotationsProperties\Foo::class,
						'type' => 'AnnotationsProperties\Foo|AnnotationsProperties\Bar',
					],
					'conflictingProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Dolor',
					],
					'bazProperty' => [
						'class' => \AnnotationsProperties\Baz::class,
						'type' => 'AnnotationsProperties\Lorem',
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
		$broker = $this->getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		foreach ($properties as $propertyName => $expectedPropertyData) {
			$this->assertTrue($class->hasProperty($propertyName));

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
		}
	}

}
