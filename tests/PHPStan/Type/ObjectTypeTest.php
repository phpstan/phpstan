<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ObjectTypeTest extends \PHPStan\TestCase
{

	public function dataIsCallable(): array
	{
		return [
			[new ObjectType('Closure'), TrinaryLogic::createYes()],
			[new ObjectType('Unknown'), TrinaryLogic::createMaybe()],
			[new ObjectType('DateTime'), TrinaryLogic::createNo()],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param ObjectType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(ObjectType $type, TrinaryLogic $expectedResult)
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
