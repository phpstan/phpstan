<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;

class ClassReflectionTest extends \PHPStan\Testing\TestCase
{

	public function dataHasTraitUse(): array
	{
		return [
			[\HasTraitUse\Foo::class, true],
			[\HasTraitUse\Bar::class, true],
			[\HasTraitUse\Baz::class, false],
		];
	}

	/**
	 * @dataProvider dataHasTraitUse
	 * @param string $className
	 * @param bool $has
	 */
	public function testHasTraitUse(string $className, bool $has)
	{
		$broker = $this->createMock(Broker::class);
		$classReflection = new ClassReflection($broker, [], [], $className, new \ReflectionClass($className), false);
		$this->assertSame($has, $classReflection->hasTraitUse(\HasTraitUse\FooTrait::class));
	}

	public function dataClassHierarchyDistances(): array
	{
		return [
			[
				\HierarchyDistances\Lorem::class,
				[
					\HierarchyDistances\Lorem::class => 0,
					\HierarchyDistances\FirstLoremInterface::class => 1,
					\HierarchyDistances\SecondLoremInterface::class => 2,
				],
			],
			[
				\HierarchyDistances\Ipsum::class,
				[
					\HierarchyDistances\Ipsum::class => 0,
					\HierarchyDistances\Lorem::class => 1,
					\HierarchyDistances\SecondLoremInterface::class => 2,
					\HierarchyDistances\FirstLoremInterface::class => 3,
					\HierarchyDistances\FirstIpsumInterface::class => 4,
					\HierarchyDistances\ExtendedIpsumInterface::class => 5,
					\HierarchyDistances\SecondIpsumInterface::class => 6,
					\HierarchyDistances\ThirdIpsumInterface::class => 7,
				],
			],
		];
	}

	/**
	 * @dataProvider dataClassHierarchyDistances
	 * @param string $class
	 * @param int[] $expectedDistances
	 */
	public function testClassHierarchyDistances(
		string $class,
		array $expectedDistances
	)
	{
		$classReflection = new ClassReflection(
			$this->createBroker(),
			[],
			[],
			$class,
			new \ReflectionClass($class),
			false
		);
		$this->assertSame(
			$expectedDistances,
			$classReflection->getClassHierarchyDistances()
		);
	}

}
