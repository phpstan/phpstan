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
		$this->createBroker();

		$intersectionType = new IntersectionType([
			new ObjectType('Collection'),
			new IterableType(new MixedType(), new ObjectType('Item')),
		]);

		yield [
			$intersectionType,
			$intersectionType,
			true,
		];

		yield [
			$intersectionType,
			new ObjectType('Collection'),
			false,
		];

		yield [
			$intersectionType,
			new IterableType(new MixedType(), new ObjectType('Item')),
			false,
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param IntersectionType $type
	 * @param Type $otherType
	 * @param bool $expectedResult
	 */
	public function testAccepts(IntersectionType $type, Type $otherType, bool $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->accepts($otherType);
		$this->assertSame(
			$expectedResult,
			$actualResult,
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
					new ArrayType(new MixedType(), new MixedType(), false),
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
		$this->createBroker();

		$actualResult = $type->isCallable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsSuperTypeOf(): \Iterator
	{
		$this->createBroker();

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
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsSubTypeOf(): \Iterator
	{
		$this->createBroker();

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
		$this->createBroker();

		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
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
		$this->createBroker();

		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::value()), $type->describe(VerbosityLevel::value()))
		);
	}

}
