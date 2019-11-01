<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\Type\FileTypeMapper;

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
	public function testHasTraitUse(string $className, bool $has): void
	{
		$broker = $this->createMock(Broker::class);
		$fileTypeMapper = $this->createMock(FileTypeMapper::class);
		$classReflection = new ClassReflection($broker, $fileTypeMapper, [], [], $className, new \ReflectionClass($className), null, null, null);
		$this->assertSame($has, $classReflection->hasTraitUse(\HasTraitUse\FooTrait::class));
	}

	public function dataClassHierarchyDistances(): array
	{
		return [
			[
				\HierarchyDistances\Lorem::class,
				[
					\HierarchyDistances\Lorem::class => 0,
					\HierarchyDistances\TraitTwo::class => 1,
					\HierarchyDistances\FirstLoremInterface::class => 2,
					\HierarchyDistances\SecondLoremInterface::class => 3,
				],
			],
			[
				\HierarchyDistances\Ipsum::class,
				PHP_VERSION_ID < 70400 ? [
					\HierarchyDistances\Ipsum::class => 0,
					\HierarchyDistances\TraitOne::class => 1,
					\HierarchyDistances\Lorem::class => 2,
					\HierarchyDistances\TraitTwo::class => 3,
					\HierarchyDistances\SecondLoremInterface::class => 4,
					\HierarchyDistances\FirstLoremInterface::class => 5,
					\HierarchyDistances\FirstIpsumInterface::class => 6,
					\HierarchyDistances\ExtendedIpsumInterface::class => 7,
					\HierarchyDistances\SecondIpsumInterface::class => 8,
					\HierarchyDistances\ThirdIpsumInterface::class => 9,
				] : [
					\HierarchyDistances\Ipsum::class => 0,
					\HierarchyDistances\TraitOne::class => 1,
					\HierarchyDistances\Lorem::class => 2,
					\HierarchyDistances\TraitTwo::class => 3,
					\HierarchyDistances\FirstLoremInterface::class => 4,
					\HierarchyDistances\SecondLoremInterface::class => 5,
					\HierarchyDistances\FirstIpsumInterface::class => 6,
					\HierarchyDistances\SecondIpsumInterface::class => 7,
					\HierarchyDistances\ThirdIpsumInterface::class => 8,
					\HierarchyDistances\ExtendedIpsumInterface::class => 9,
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
	): void
	{
		$broker = $this->createBroker();
		$fileTypeMapper = $this->createMock(FileTypeMapper::class);

		$classReflection = new ClassReflection(
			$broker,
			$fileTypeMapper,
			[],
			[],
			$class,
			new \ReflectionClass($class),
			null,
			null
		);
		$this->assertSame(
			$expectedDistances,
			$classReflection->getClassHierarchyDistances()
		);
	}

	public function testVariadicTraitMethod(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getService('broker');
		$fooReflection = $broker->getClass(\HasTraitUse\Foo::class);
		$variadicMethod = $fooReflection->getNativeMethod('variadicMethod');
		$methodVariant = ParametersAcceptorSelector::selectSingle($variadicMethod->getVariants());
		$this->assertTrue($methodVariant->isVariadic());
	}

	public function testGenericInheritance(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getService('broker');
		$reflection = $broker->getClass(\GenericInheritance\C::class);

		$this->assertSame('GenericInheritance\\C', $reflection->getDisplayName());

		$parent = $reflection->getParentClass();
		$this->assertNotFalse($parent);

		$this->assertSame('GenericInheritance\\C0<DateTime>', $parent->getDisplayName());

		$this->assertSame([
			'GenericInheritance\\I0<DateTime>',
			'GenericInheritance\\I1<int>',
			'GenericInheritance\\I<DateTime>',
		], array_map(static function (ClassReflection $r): string {
			return $r->getDisplayName();
		}, array_values($reflection->getInterfaces())));
	}

	public function testGenericInheritanceOverride(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getService('broker');
		$reflection = $broker->getClass(\GenericInheritance\Override::class);

		$this->assertSame([
			'GenericInheritance\\I0<DateTimeInterface>',
			'GenericInheritance\\I1<int>',
			'GenericInheritance\\I<DateTimeInterface>',
		], array_map(static function (ClassReflection $r): string {
			return $r->getDisplayName();
		}, array_values($reflection->getInterfaces())));
	}

}
