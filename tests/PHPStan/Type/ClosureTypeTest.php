<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ClosureTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ClosureType(new MixedType()),
				new ObjectType(\Closure::class),
				TrinaryLogic::createNo(),
			],
			[
				new ClosureType(new MixedType()),
				new ClosureType(new MixedType()),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType(new UnionType([new IntegerType(), new StringType()])),
				new ClosureType(new IntegerType()),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType(new MixedType()),
				new CallableType(),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ClosureType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(
		ClosureType $type,
		Type $otherType,
		TrinaryLogic $expectedResult
	): void
	{
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

	public function dataIsSubTypeOf(): array
	{
		return [
			[
				new ClosureType(new MixedType()),
				new ObjectType(\Closure::class),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType(new MixedType()),
				new ClosureType(new MixedType()),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType(new UnionType([new IntegerType(), new StringType()])),
				new ClosureType(new IntegerType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClosureType(new IntegerType()),
				new ClosureType(new UnionType([new IntegerType(), new StringType()])),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType(new MixedType()),
				new CallableType(),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param ClosureType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(
		ClosureType $type,
		Type $otherType,
		TrinaryLogic $expectedResult
	): void
	{
		$this->createBroker();

		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

}
