<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class UnionTypeTest extends \PHPStan\TestCase
{

	public function dataIsCallable(): array
	{
		return [
			[
				new CommonUnionType([
					new ArrayType(new MixedType(), false, TrinaryLogic::createYes()),
					new StringType(),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CommonUnionType([
					new ArrayType(new MixedType(), false, TrinaryLogic::createYes()),
					new ObjectType('Closure'),
				]),
				TrinaryLogic::createYes(),
			],
			[
				new CommonUnionType([
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
	public function testIsCallable(UnionType $type, TrinaryLogic $expectedResult)
	{
		$this->createBroker();

		$actualResult = $type->isCallable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe())
		);
	}

}
