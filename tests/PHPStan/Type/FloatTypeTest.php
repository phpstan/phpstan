<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FloatTypeTest extends \PHPStan\TestCase
{

	public function testAccepts()
	{
		$floatType = new FloatType(true);

		$this->assertTrue($floatType->accepts(new FloatType(false)));
		$this->assertTrue($floatType->accepts(new IntegerType(false)));
		$this->assertTrue($floatType->accepts(new NullType()));
		$this->assertFalse((new FloatType(false))->accepts(new NullType()));
		$this->assertFalse($floatType->accepts(new StringType(false)));
	}

	public function testCombineWith()
	{
		$floatType = new FloatType(false);

		$this->assertInstanceOf(FloatType::class, $floatType->combineWith(new FloatType(false)));
		$this->assertInstanceOf(FloatType::class, $floatType->combineWith(new IntegerType(false)));
		$this->assertInstanceOf(FloatType::class, $floatType->combineWith(new NullType()));
		$this->assertInstanceOf(MixedType::class, $floatType->combineWith(new StringType(false)));
	}

}
