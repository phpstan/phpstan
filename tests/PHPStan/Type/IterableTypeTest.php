<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IterableTypeTest extends \PHPStan\TestCase
{

	public function dataIsSubsetOf(): array
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
				new UnionType([new ArrayType(new MixedType()), new ObjectType('Traversable')]),
				TrinaryLogic::createYes(),
			],
			[
				new IterableIterableType(new StringType()),
				new UnionType([new ArrayType(new StringType()), new ObjectType('Traversable')]),
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
	 * @dataProvider dataIsSubsetOf
	 * @param IterableIterableType $type
	 * @param Type $otherType
	 * @param int $expectedResult
	 */
	public function testIsSubsetOf(IterableIterableType $type, Type $otherType, TrinaryLogic $expectedResult)
	{
		$this->createBroker();

		$actualResult = $type->isSubsetOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubsetOf(%s)', $type->describe(), $otherType->describe())
		);
	}

	/**
	 * @dataProvider dataIsSubsetOf
	 * @param IterableIterableType $type
	 * @param Type $otherType
	 * @param int $expectedResult
	 */
	public function testIsSubsetOfInversed(IterableIterableType $type, Type $otherType, TrinaryLogic $expectedResult)
	{
		$this->createBroker();

		$actualResult = $otherType->isSupersetOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSupersetOf(%s)', $otherType->describe(), $type->describe())
		);
	}

}
