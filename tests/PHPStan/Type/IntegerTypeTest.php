<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerTypeTest extends \PHPStan\Testing\TestCase
{

	public function testAccepts()
	{
		$integerType = new IntegerType();

		$this->assertTrue($integerType->accepts(new IntegerType()));
		$this->assertFalse($integerType->accepts(new NullType()));
		$this->assertTrue($integerType->accepts(new MixedType()));
		$this->assertFalse($integerType->accepts(new FloatType()));
		$this->assertFalse($integerType->accepts(new StringType()));
	}

}
