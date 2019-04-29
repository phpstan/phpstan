<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ObjectWithoutClassTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ObjectWithoutClassType(),
				new ObjectWithoutClassType(),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectWithoutClassType(),
				new ObjectType('Exception'),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectWithoutClassType(new ObjectType('Exception')),
				new ObjectType('Exception'),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectWithoutClassType(new ObjectType(\InvalidArgumentException::class)),
				new ObjectType('Exception'),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectWithoutClassType(new ObjectType('Exception')),
				new ObjectType(\InvalidArgumentException::class),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectWithoutClassType(),
				new ObjectWithoutClassType(new ObjectType('Exception')),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectWithoutClassType(new ObjectType('Exception')),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectWithoutClassType(new ObjectType(\InvalidArgumentException::class)),
				new ObjectWithoutClassType(new ObjectType('Exception')),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectWithoutClassType(new ObjectType('Exception')),
				new ObjectWithoutClassType(new ObjectType(\InvalidArgumentException::class)),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ObjectWithoutClassType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ObjectWithoutClassType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
