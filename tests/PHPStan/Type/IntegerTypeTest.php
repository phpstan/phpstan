<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantIntegerType;

class IntegerTypeTest extends \PHPStan\Testing\TestCase
{

	public function testAccepts(): void
	{
		$integerType = new IntegerType();

		$this->assertTrue($integerType->accepts(new IntegerType()));
		$this->assertTrue($integerType->accepts(new ConstantIntegerType(1)));
		$this->assertFalse($integerType->accepts(new NullType()));
		$this->assertTrue($integerType->accepts(new MixedType()));
		$this->assertFalse($integerType->accepts(new FloatType()));
		$this->assertFalse($integerType->accepts(new StringType()));
	}


	public function dataIsSuperTypeOf(): iterable
	{
		yield [
			new IntegerType(),
			new IntegerType(),
			TrinaryLogic::createYes(),
		];

		yield [
			new IntegerType(),
			new ConstantIntegerType(1),
			TrinaryLogic::createYes(),
		];

		yield [
			new IntegerType(),
			new MixedType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new IntegerType(),
			new UnionType([new IntegerType(), new StringType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new IntegerType(),
			new StringType(),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param IntegerType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(IntegerType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

}
