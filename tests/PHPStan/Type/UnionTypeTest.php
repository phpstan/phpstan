<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantBooleanType;

class UnionTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsCallable(): array
	{
		return [
			[
				new UnionType([
					new ArrayType(new MixedType(), new MixedType(), false, TrinaryLogic::createYes()),
					new StringType(),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new UnionType([
					new ArrayType(new MixedType(), new MixedType(), false, TrinaryLogic::createYes()),
					new ObjectType('Closure'),
				]),
				TrinaryLogic::createYes(),
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
				]),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param UnionType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(UnionType $type, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->isCallable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe())
		);
	}

	public function dataIsSuperTypeOf(): \Iterator
	{
		$this->createBroker();

		$unionTypeA = new UnionType([
			new IntegerType(),
			new StringType(),
		]);

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[0],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[1],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new MixedType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new IntegerType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new CallableType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new UnionType([new ConstantBooleanType(true), new FloatType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new ArrayType(new MixedType(), new MixedType()), new CallableType()]),
			TrinaryLogic::createNo(),
		];

		$unionTypeB = new UnionType([
			new IntersectionType([
				new ObjectType('ArrayObject'),
				new IterableType(new MixedType(), new ObjectType('DatePeriod')),
			]),
			new ArrayType(new MixedType(), new ObjectType('DatePeriod')),
		]);

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[0],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[1],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new MixedType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new ObjectType('DatePeriod')),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new StringType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new IntegerType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new ObjectType('Foo'),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new ObjectType('DateTime')),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(), $otherType->describe())
		);
	}

	public function dataIsSubTypeOf(): \Iterator
	{
		$this->createBroker();

		$unionTypeA = new UnionType([
			new IntegerType(),
			new StringType(),
		]);

		yield [
			$unionTypeA,
			$unionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new UnionType(array_merge($unionTypeA->getTypes(), [new ResourceType()])),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new MixedType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[0],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[1],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new IntegerType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new CallableType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new UnionType([new ConstantBooleanType(true), new FloatType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new ArrayType(new MixedType(), new MixedType()), new CallableType()]),
			TrinaryLogic::createNo(),
		];

		$unionTypeB = new UnionType([
			new IntersectionType([
				new ObjectType('ArrayObject'),
				new IterableType(new MixedType(), new ObjectType('Item')),
				new CallableType(),
			]),
			new ArrayType(new MixedType(), new ObjectType('Item')),
		]);

		yield [
			$unionTypeB,
			$unionTypeB,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new UnionType(array_merge($unionTypeB->getTypes(), [new StringType()])),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new MixedType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[0],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[1],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new ObjectType('Foo'),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(), $otherType->describe())
		);
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$this->createBroker();

		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(), $type->describe())
		);
	}

}
