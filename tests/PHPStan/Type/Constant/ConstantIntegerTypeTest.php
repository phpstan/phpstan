<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantIntegerTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): iterable
	{
		yield [
			new ConstantIntegerType(1),
			new ConstantIntegerType(1),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantIntegerType(1),
			new IntegerType(),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantIntegerType(1),
			new ConstantIntegerType(2),
			TrinaryLogic::createNo(),
		];
	}


	/**
	 * @dataProvider dataAccepts
	 * @param ConstantIntegerType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(ConstantIntegerType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}


	public function dataIsSuperTypeOf(): iterable
	{
		yield [
			new ConstantIntegerType(1),
			new ConstantIntegerType(1),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantIntegerType(1),
			new IntegerType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantIntegerType(1),
			new ConstantIntegerType(2),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ConstantIntegerType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ConstantIntegerType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

}
