<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class CallableTypeTest extends \PHPStan\TestCase
{

	public function dataIsSubsetOf(): array
	{
		return [
			[
				new CallableType(),
				new CallableType(),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new StringType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new UnionType([new CallableType(), new NullType()]),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new UnionType([new StringType(), new NullType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new UnionType([new IntegerType(), new NullType()]),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType()]),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new IntersectionType([new StringType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntersectionType([new IntegerType()]),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType(), new StringType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType(), new ObjectType('Unknown')]),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubsetOf
	 * @param CallableType $type
	 * @param Type $otherType
	 * @param int $expectedResult
	 */
	public function testIsSubsetOf(CallableType $type, Type $otherType, TrinaryLogic $expectedResult)
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
	 * @param CallableType $type
	 * @param Type $otherType
	 * @param int $expectedResult
	 */
	public function testIsSubsetOfInversed(CallableType $type, Type $otherType, TrinaryLogic $expectedResult)
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
