<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;

class IterableTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new IterableType(new IntegerType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new IntegerType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableType(new StringType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createNo(),
			],
			[
				new IterableType(new StringType(), new StringType()),
				new ConstantArrayType([], []),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param IterableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(IterableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSubTypeOf(): array
	{
		return [
			[
				new IterableType(new MixedType(), new StringType()),
				new IterableType(new MixedType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new ObjectType('Unknown'),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new IterableType(new MixedType(), new IntegerType()),
				TrinaryLogic::createNo(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new IterableType(new MixedType(), new StringType()), new NullType()]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new ArrayType(new MixedType(), new MixedType()), new ObjectType('Traversable')]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new ArrayType(new MixedType(), new StringType()), new ObjectType('Traversable')]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new ObjectType('Unknown'), new NullType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new IntegerType(), new NullType()]),
				TrinaryLogic::createNo(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new UnionType([new IterableType(new MixedType(), new IntegerType()), new NullType()]),
				TrinaryLogic::createNo(),
			],
			[
				new IterableType(new IntegerType(), new StringType()),
				new IterableType(new MixedType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new IterableType(new MixedType(), new StringType()),
				new IterableType(new IntegerType(), new StringType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableType(new StringType(), new StringType()),
				new IterableType(new IntegerType(), new StringType()),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param IterableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(IterableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param IterableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(IterableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::precise()), $type->describe(VerbosityLevel::precise()))
		);
	}

}
