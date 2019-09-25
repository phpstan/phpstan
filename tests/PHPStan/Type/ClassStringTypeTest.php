<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Testing\TestCase;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantStringType;

class ClassStringTypeTest extends TestCase
{

	public function dataAccepts(): iterable
	{
		yield [
			new ClassStringType(),
			new ClassStringType(),
			TrinaryLogic::createYes(),
		];

		yield [
			new ClassStringType(),
			new StringType(),
			TrinaryLogic::createNo(),
		];

		yield [
			new ClassStringType(),
			new IntegerType(),
			TrinaryLogic::createNo(),
		];

		yield [
			new ClassStringType(),
			new ConstantStringType(\stdClass::class),
			TrinaryLogic::createYes(),
		];

		yield [
			new ClassStringType(),
			new ConstantStringType('NonexistentClass'),
			TrinaryLogic::createNo(),
		];

		yield [
			new ClassStringType(),
			new UnionType([new ConstantStringType(\stdClass::class), new ConstantStringType(self::class)]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ClassStringType(),
			new UnionType([new ConstantStringType(\stdClass::class), new ConstantStringType('Nonexistent')]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ClassStringType(),
			new UnionType([new ConstantStringType('Nonexistent'), new ConstantStringType('Nonexistent2')]),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param \PHPStan\Type\ClassStringType $type
	 * @param Type $otherType
	 * @param \PHPStan\TrinaryLogic $expectedResult
	 */
	public function testAccepts(ClassStringType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
