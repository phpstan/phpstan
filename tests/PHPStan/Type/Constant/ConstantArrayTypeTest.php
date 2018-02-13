<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class ConstantArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): iterable
	{
		yield [
			new ConstantArrayType([], [], new IntegerType()),
			new ConstantArrayType([], [], new IntegerType()),
			true,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			true,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([], [], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([], [], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new IntegerType()], [new IntegerType()], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new StringType()], [new StringType()], new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new MixedType()], [new MixedType()], new IntegerType()),
			true,
		];
	}


	/**
	 * @dataProvider dataAccepts
	 * @param ConstantArrayType $type
	 * @param Type $otherType
	 * @param bool $expectedResult
	 */
	public function testAccepts(ConstantArrayType $type, Type $otherType, bool $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->accepts($otherType);
		$this->assertSame(
			$expectedResult,
			$actualResult,
			sprintf('%s -> accepts(%s)', $type->describe(), $otherType->describe())
		);
	}


	public function dataIsSuperTypeOf(): iterable
	{
		yield [
			new ConstantArrayType([], [], new IntegerType()),
			new ConstantArrayType([], [], new IntegerType()),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([], [], new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([], [], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)], new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)], new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new IntegerType()], [new IntegerType()], new IntegerType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new StringType()], [new StringType()], new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)], new IntegerType()),
			new ConstantArrayType([new MixedType()], [new MixedType()], new IntegerType()),
			TrinaryLogic::createMaybe(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ConstantArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ConstantArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(), $otherType->describe())
		);
	}

}
