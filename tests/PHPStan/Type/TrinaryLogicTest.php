<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TrinaryLogicTest extends \PHPStan\TestCase
{

	public function dataAnd(): array
	{
		return [
			[TrinaryLogic::NO, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::YES],

			[TrinaryLogic::NO, TrinaryLogic::NO, TrinaryLogic::NO],
			[TrinaryLogic::NO, TrinaryLogic::NO, TrinaryLogic::MAYBE],
			[TrinaryLogic::NO, TrinaryLogic::NO, TrinaryLogic::YES],

			[TrinaryLogic::NO, TrinaryLogic::MAYBE, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE, TrinaryLogic::MAYBE],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE, TrinaryLogic::YES],

			[TrinaryLogic::NO, TrinaryLogic::YES, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::YES, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::YES, TrinaryLogic::YES],
		];
	}

	/**
	 * @dataProvider dataAnd
	 * @param int $expectedResult
	 * @param int ...$operands
	 */
	public function testAnd(int $expectedResult, int ...$operands)
	{
		$this->assertSame($expectedResult, TrinaryLogic::and(...$operands));
	}

	public function dataOr(): array
	{
		return [
			[TrinaryLogic::NO, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::YES],

			[TrinaryLogic::NO, TrinaryLogic::NO, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::NO, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::NO, TrinaryLogic::YES],

			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE, TrinaryLogic::NO],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::MAYBE, TrinaryLogic::YES],

			[TrinaryLogic::YES, TrinaryLogic::YES, TrinaryLogic::NO],
			[TrinaryLogic::YES, TrinaryLogic::YES, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::YES, TrinaryLogic::YES],
		];
	}

	/**
	 * @dataProvider dataOr
	 * @param int $expectedResult
	 * @param int ...$operands
	 */
	public function testOr(int $expectedResult, int ...$operands)
	{
		$this->assertSame($expectedResult, TrinaryLogic::or(...$operands));
	}

	public function dataNot(): array
	{
		return [
			[TrinaryLogic::NO, TrinaryLogic::YES],
			[TrinaryLogic::MAYBE, TrinaryLogic::MAYBE, TrinaryLogic::MAYBE],
			[TrinaryLogic::YES, TrinaryLogic::NO],
		];
	}

	/**
	 * @dataProvider dataNot
	 * @param int $expectedResult
	 * @param int $operand
	 */
	public function testNot(int $expectedResult, int $operand)
	{
		$this->assertSame($expectedResult, TrinaryLogic::not($operand));
	}

}
