<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class ConstantArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): iterable
	{
		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([], []),
			true,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			true,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			false,
		];

		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)]),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)]),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new IntegerType(), new IntegerType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new StringType(), new StringType()),
			false,
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new MixedType(), new MixedType()),
			false,
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
			new ConstantArrayType([], []),
			new ConstantArrayType([], []),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new IntegerType(), new IntegerType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new StringType(), new StringType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new MixedType(), new MixedType()),
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
