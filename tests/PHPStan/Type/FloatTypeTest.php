<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FloatTypeTest extends \PHPStan\TestCase
{

	public function testAccepts()
	{
		$floatType = new FloatType();

		$this->assertTrue($floatType->accepts(new FloatType()));
		$this->assertFalse($floatType->accepts(new NullType()));
		$this->assertTrue($floatType->accepts(new MixedType()));
		$this->assertTrue($floatType->accepts(new IntegerType()));
		$this->assertFalse($floatType->accepts(new StringType()));
	}

}
