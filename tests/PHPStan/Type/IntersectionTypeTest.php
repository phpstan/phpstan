<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class IntersectionTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): \Iterator
	{
		$intersectionType = new IntersectionType([
			new ObjectType('Collection'),
			new IterableType(new MixedType(), new ObjectType('Item')),
		]);

		yield [
			$intersectionType,
			$intersectionType,
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionType,
			new ObjectType('Collection'),
			TrinaryLogic::createNo(),
		];

		yield [
			$intersectionType,
			new IterableType(new MixedType(), new ObjectType('Item')),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param IntersectionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(IntersectionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->accepts($otherType, true);
		self::assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsCallable(): array
	{
		return [
			[
				new IntersectionType([
					new ConstantArrayType(
						[new ConstantIntegerType(0), new ConstantIntegerType(1)],
						[new ConstantStringType('Closure'), new ConstantStringType('bind')]
					),
					new IterableType(new MixedType(), new ObjectType('Item')),
				]),
				TrinaryLogic::createYes(),
			],
			[
				new IntersectionType([
					new ArrayType(new MixedType(), new MixedType()),
					new IterableType(new MixedType(), new ObjectType('Item')),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new IntersectionType([
					new ObjectType('ArrayObject'),
					new IterableType(new MixedType(), new ObjectType('Item')),
				]),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param IntersectionType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(IntersectionType $type, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isCallable();
		self::assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsSuperTypeOf(): \Iterator
	{
		$intersectionTypeA = new IntersectionType([
			new ObjectType('ArrayObject'),
			new IterableType(new MixedType(), new ObjectType('Item')),
		]);

		yield [
			$intersectionTypeA,
			$intersectionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeA,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$intersectionTypeA,
			new IterableType(new MixedType(), new ObjectType('Item')),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$intersectionTypeA,
			new ArrayType(new MixedType(), new ObjectType('Item')),
			TrinaryLogic::createNo(),
		];

		$intersectionTypeB = new IntersectionType([
			new IntegerType(),
		]);

		yield [
			$intersectionTypeB,
			$intersectionTypeB,
			TrinaryLogic::createYes(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param IntersectionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(IntersectionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		self::assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsSubTypeOf(): \Iterator
	{
		$intersectionTypeA = new IntersectionType([
			new ObjectType('ArrayObject'),
			new IterableType(new MixedType(), new ObjectType('Item')),
		]);

		yield [
			$intersectionTypeA,
			$intersectionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeA,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeA,
			new IterableType(new MixedType(), new ObjectType('Item')),
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeA,
			new MixedType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeA,
			new IterableType(new MixedType(), new ObjectType('Unknown')),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$intersectionTypeA,
			new ArrayType(new MixedType(), new ObjectType('Item')),
			TrinaryLogic::createNo(),
		];

		$intersectionTypeB = new IntersectionType([
			new IntegerType(),
		]);

		yield [
			$intersectionTypeB,
			$intersectionTypeB,
			TrinaryLogic::createYes(),
		];

		$intersectionTypeC = new IntersectionType([
			new StringType(),
			new CallableType(),
		]);

		yield [
			$intersectionTypeC,
			$intersectionTypeC,
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeC,
			new StringType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeC,
			new UnionType([new IntegerType(), new StringType()]),
			TrinaryLogic::createYes(),
		];

		$intersectionTypeD = new IntersectionType([
			new ObjectType('ArrayObject'),
			new IterableType(new MixedType(), new ObjectType('DatePeriod')),
		]);

		yield [
			$intersectionTypeD,
			$intersectionTypeD,
			TrinaryLogic::createYes(),
		];

		yield [
			$intersectionTypeD,
			new UnionType([
				$intersectionTypeD,
				new IntegerType(),
			]),
			TrinaryLogic::createYes(),
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param IntersectionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(IntersectionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSubTypeOf($otherType);
		self::assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param IntersectionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(IntersectionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $otherType->isSuperTypeOf($type);
		self::assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::value()), $type->describe(VerbosityLevel::value()))
		);
	}

}
