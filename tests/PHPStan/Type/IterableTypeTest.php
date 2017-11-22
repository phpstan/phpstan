<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IterableTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSubTypeOf(): array
	{
		return [
			[
				new IterableIterableType(new StringType()),
				new IterableIterableType(new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new IterableIterableType(new StringType()),
				new ObjectType('Unknown'),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableIterableType(new StringType()),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
			[
				new IterableIterableType(new StringType()),
				new IterableIterableType(new IntegerType()),
				TrinaryLogic::createNo(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new IterableIterableType(new StringType()), new NullType()]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new ArrayType(new MixedType(), new MixedType()), new ObjectType('Traversable')]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new ArrayType(new MixedType(), new StringType()), new ObjectType('Traversable')]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new ObjectType('Unknown'), new NullType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new IntegerType(), new NullType()]),
				TrinaryLogic::createNo(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new IterableIterableType(new IntegerType()), new NullType()]),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param IterableIterableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(IterableIterableType $type, Type $otherType, TrinaryLogic $expectedResult)
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
	 * @param IterableIterableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(IterableIterableType $type, Type $otherType, TrinaryLogic $expectedResult)
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
