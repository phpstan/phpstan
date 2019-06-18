<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): iterable
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
			TrinaryLogic::createNo(),
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

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new IterableType(new MixedType(), new IntegerType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new CallableType()]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('foo'), new ConstantStringType('bar')], [new StringType(), new StringType()]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('bar')], [new StringType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('foo')], [new ConstantStringType('bar')]),
			TrinaryLogic::createYes(),
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param ConstantArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(ConstantArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
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
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
