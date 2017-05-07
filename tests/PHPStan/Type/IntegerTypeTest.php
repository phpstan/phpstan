<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerTypeTest extends \PHPStan\TestCase
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

	public function testCombineWith()
	{
		$integerType = new IntegerType();

		$this->assertSame('int', $integerType->combineWith(new IntegerType())->describe());
		$this->assertSame('int|null', $integerType->combineWith(new NullType())->describe());
		$this->assertSame('float|int', $integerType->combineWith(new FloatType())->describe());
		$this->assertSame('int|string', $integerType->combineWith(new StringType())->describe());
	}

}
