<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class StaticTypeTest extends \PHPStan\TestCase
{

	public function dataIsCallable(): array
	{
		return [
			[new StaticType('Closure'), TrinaryLogic::createYes()],
			[new StaticType('Unknown'), TrinaryLogic::createMaybe()],
			[new StaticType('DateTime'), TrinaryLogic::createNo()],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param StaticType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(StaticType $type, TrinaryLogic $expectedResult)
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
