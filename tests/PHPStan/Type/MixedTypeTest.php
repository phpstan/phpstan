<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantIntegerType;

class MixedTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new MixedType(),
				new MixedType(),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(),
				new IntegerType(),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new IntegerType()),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
			[
				new MixedType(false, new IntegerType()),
				new ConstantIntegerType(1),
				TrinaryLogic::createNo(),
			],
			[
				new MixedType(false, new ConstantIntegerType(1)),
				new IntegerType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ConstantIntegerType(1)),
				new MixedType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(),
				new MixedType(false, new ConstantIntegerType(1)),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new ConstantIntegerType(1)),
				new MixedType(false, new ConstantIntegerType(1)),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new IntegerType()),
				new MixedType(false, new ConstantIntegerType(1)),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ConstantIntegerType(1)),
				new MixedType(false, new IntegerType()),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new StringType()),
				new MixedType(false, new IntegerType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(),
				new ObjectWithoutClassType(),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new ObjectWithoutClassType()),
				new ObjectWithoutClassType(),
				TrinaryLogic::createNo(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new ObjectWithoutClassType(new ObjectType('Exception')),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new ObjectWithoutClassType(new ObjectType('InvalidArgumentException')),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ObjectType('InvalidArgumentException')),
				new ObjectWithoutClassType(new ObjectType('Exception')),
				TrinaryLogic::createYes(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new ObjectType('Exception'),
				TrinaryLogic::createNo(),
			],
			[
				new MixedType(false, new ObjectType('InvalidArgumentException')),
				new ObjectType('Exception'),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new ObjectType('InvalidArgumentException'),
				TrinaryLogic::createNo(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new MixedType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new MixedType(false, new ObjectType('Exception')),
				new MixedType(false, new ObjectType('stdClass')),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param \PHPStan\Type\MixedType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(MixedType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
