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

	public function testCombineWith()
	{
		$floatType = new FloatType();

		$this->assertSame('float', $floatType->combineWith(new FloatType())->describe());
		$this->assertSame('float', $floatType->combineWith(new IntegerType())->describe());
		$this->assertSame('float|null', $floatType->combineWith(new NullType())->describe());
		$this->assertSame('float|string', $floatType->combineWith(new StringType())->describe());
	}

}
