<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;

class ArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new IntegerType()),
				TrinaryLogic::createNo(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new StringType(), new StringType()),
				TrinaryLogic::createNo(),
			],
			[
				new ArrayType(new MixedType(), new MixedType()),
				new CallableType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ConstantArrayType([], []),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataAccepts(): array
	{
		return [
			[
				new ArrayType(new MixedType(), new StringType()),
				new UnionType([
					new ConstantArrayType([], []),
					new ConstantArrayType(
						[new ConstantIntegerType(0)],
						[new MixedType()]
					),
					new ConstantArrayType([
						new ConstantIntegerType(0),
						new ConstantIntegerType(1),
					], [
						new StringType(),
						new MixedType(),
					]),
				]),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param ArrayType $acceptingType
	 * @param Type $acceptedType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(
		ArrayType $acceptingType,
		Type $acceptedType,
		TrinaryLogic $expectedResult
	): void
	{
		$actualResult = $acceptingType->accepts($acceptedType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $acceptingType->describe(VerbosityLevel::precise()), $acceptedType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataDescribe(): array
	{
		return [
			[
				new ArrayType(new BenevolentUnionType([
					new IntegerType(),
					new StringType(),
				]), new IntegerType()),
				'array<int>',
			],
		];
	}

	/**
	 * @dataProvider dataDescribe
	 * @param ArrayType $type
	 * @param string $expectedDescription
	 */
	public function testDescribe(
		ArrayType $type,
		string $expectedDescription
	): void
	{
		$this->assertSame($expectedDescription, $type->describe(VerbosityLevel::precise()));
	}

}
