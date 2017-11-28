<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new IntegerType()),
				TrinaryLogic::createNo(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new StringType(), new StringType()),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ArrayType $type, Type $otherType, TrinaryLogic $expectedResult)
	{
		$this->createBroker();

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(), $otherType->describe())
		);
	}

}
