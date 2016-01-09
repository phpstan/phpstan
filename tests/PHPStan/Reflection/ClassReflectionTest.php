<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;

class ClassReflectionTest extends \PHPStan\TestCase
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
		$classReflection = new ClassReflection($broker, [], [], new \ReflectionClass($className));
		$this->assertSame($has, $classReflection->hasTraitUse(\HasTraitUse\FooTrait::class));
	}

}
