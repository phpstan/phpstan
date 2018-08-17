<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ClosureTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ClosureType([], new MixedType(), false),
				new ObjectType(\Closure::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClosureType([], new MixedType(), false),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
				new ClosureType([], new IntegerType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType([], new MixedType(), false),
				new CallableType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\Closure::class),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType([], new MixedType(), false),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType([], new IntegerType(), false),
				new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
				TrinaryLogic::createMaybe(),
			],
			[
				new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
				new ClosureType([], new IntegerType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param Type $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(
		Type $type,
		Type $otherType,
		TrinaryLogic $expectedResult
	): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
		);
	}

}
