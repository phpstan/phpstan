<?php declare(strict_types = 1);

namespace PHPStan;

class TrinaryLogicTest extends \PHPStan\Testing\TestCase
{

	public function dataAnd(): array
	{
		return [
			[TrinaryLogic::createNo(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes()],

			[TrinaryLogic::createNo(), TrinaryLogic::createNo(), TrinaryLogic::createNo()],
			[TrinaryLogic::createNo(), TrinaryLogic::createNo(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createNo(), TrinaryLogic::createNo(), TrinaryLogic::createYes()],

			[TrinaryLogic::createNo(), TrinaryLogic::createMaybe(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe(), TrinaryLogic::createYes()],

			[TrinaryLogic::createNo(), TrinaryLogic::createYes(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createYes(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes(), TrinaryLogic::createYes()],
		];
	}

	/**
	 * @dataProvider dataAnd
	 * @param TrinaryLogic $expectedResult
	 * @param TrinaryLogic $value
	 * @param TrinaryLogic ...$operands
	 */
	public function testAnd(
		TrinaryLogic $expectedResult,
		TrinaryLogic $value,
		TrinaryLogic ...$operands
	)
	{
		$this->assertTrue($expectedResult->equals($value->and(...$operands)));
	}

	public function dataOr(): array
	{
		return [
			[TrinaryLogic::createNo(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes()],

			[TrinaryLogic::createNo(), TrinaryLogic::createNo(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createNo(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createNo(), TrinaryLogic::createYes()],

			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createMaybe(), TrinaryLogic::createYes()],

			[TrinaryLogic::createYes(), TrinaryLogic::createYes(), TrinaryLogic::createNo()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes(), TrinaryLogic::createYes()],
		];
	}

	/**
	 * @dataProvider dataOr
	 * @param TrinaryLogic $expectedResult
	 * @param TrinaryLogic $value
	 * @param TrinaryLogic ...$operands
	 */
	public function testOr(
		TrinaryLogic $expectedResult,
		TrinaryLogic $value,
		TrinaryLogic ...$operands
	)
	{
		$this->assertTrue($expectedResult->equals($value->or(...$operands)));
	}

	public function dataNegate(): array
	{
		return [
			[TrinaryLogic::createNo(), TrinaryLogic::createYes()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createNo()],
		];
	}

	/**
	 * @dataProvider dataNegate
	 * @param TrinaryLogic $expectedResult
	 * @param TrinaryLogic $operand
	 */
	public function testNegate(TrinaryLogic $expectedResult, TrinaryLogic $operand)
	{
		$this->assertTrue($expectedResult->equals($operand->negate()));
	}

	public function dataAddMaybe(): array
	{
		return [
			[TrinaryLogic::createMaybe(), TrinaryLogic::createNo()],
			[TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe()],
			[TrinaryLogic::createYes(), TrinaryLogic::createYes()],
		];
	}

	/**
	 * @dataProvider dataAddMaybe
	 * @param TrinaryLogic $expectedResult
	 * @param TrinaryLogic $value
	 */
	public function testAddMaybe(
		TrinaryLogic $expectedResult,
		TrinaryLogic $value
	)
	{
		$this->assertTrue($expectedResult->equals($value->addMaybe()));
	}

}
