<?php declare(strict_types = 1);

namespace PHPStan\Type;

class MixedTypeTest extends \PHPStan\TestCase
{

	public function testAccepts()
	{
		$mixedType = new MixedType();

		$this->assertTrue($mixedType->accepts(new IntegerType(false)));
		$this->assertTrue($mixedType->accepts(new NullType()));
		$this->assertTrue($mixedType->accepts(new MixedType()));
		$this->assertFalse($mixedType->accepts(new VoidType()));
	}

	public function testCombineWith()
	{
		$mixedType = new MixedType();

		$this->assertInstanceOf(MixedType::class, $mixedType->combineWith(new IntegerType(false)));
		$this->assertInstanceOf(MixedType::class, $mixedType->combineWith(new NullType()));
		$this->assertInstanceOf(MixedType::class, $mixedType->combineWith(new FloatType(false)));
		$this->assertInstanceOf(MixedType::class, $mixedType->combineWith(new StringType(false)));
	}

}
