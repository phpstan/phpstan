<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IntegerTypeTest extends \PHPStan\TestCase
{
    public function testAccepts()
    {
        $integerType = new IntegerType(true);

        $this->assertTrue($integerType->accepts(new IntegerType(false)));
        $this->assertTrue($integerType->accepts(new NullType()));
        $this->assertTrue($integerType->accepts(new MixedType()));
        $this->assertFalse($integerType->accepts(new FloatType(false)));
        $this->assertFalse((new FloatType(false))->accepts(new NullType()));
        $this->assertFalse($integerType->accepts(new StringType(false)));
    }

    public function testCombineWith()
    {
        $integerType = new IntegerType(false);

        $this->assertInstanceOf(IntegerType::class, $integerType->combineWith(new IntegerType(false)));
        $this->assertInstanceOf(IntegerType::class, $integerType->combineWith(new NullType()));
        $this->assertInstanceOf(FloatType::class, $integerType->combineWith(new FloatType(false)));
        $this->assertInstanceOf(MixedType::class, $integerType->combineWith(new StringType(false)));
    }
}
