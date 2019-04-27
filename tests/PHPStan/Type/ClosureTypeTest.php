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
			[
				new ObjectWithoutClassType(),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ClosureType([], new MixedType(), false),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectWithoutClassType(new ClosureType([], new MixedType(), false)),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectWithoutClassType(new ObjectType(\Closure::class)),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createNo(),
			],
			[
				new ClosureType([], new MixedType(), false),
				new ObjectWithoutClassType(new ObjectType(\Closure::class)),
				TrinaryLogic::createNo(),
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
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
