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
	): void
	{
		self::assertTrue($expectedResult->equals($value->and(...$operands)));
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
	): void
	{
		self::assertTrue($expectedResult->equals($value->or(...$operands)));
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
	public function testNegate(TrinaryLogic $expectedResult, TrinaryLogic $operand): void
	{
		self::assertTrue($expectedResult->equals($operand->negate()));
	}

	public function dataCompareTo(): array
	{
		$yes = TrinaryLogic::createYes();
		$maybe = TrinaryLogic::createMaybe();
		$no = TrinaryLogic::createNo();
		return [
			[
				$yes,
				$yes,
				null,
			],
			[
				$maybe,
				$maybe,
				null,
			],
			[
				$no,
				$no,
				null,
			],
			[
				$yes,
				$maybe,
				$yes,
			],
			[
				$yes,
				$no,
				$yes,
			],
			[
				$maybe,
				$no,
				$maybe,
			],
		];
	}

	/**
	 * @dataProvider dataCompareTo
	 * @param TrinaryLogic $first
	 * @param TrinaryLogic $second
	 * @param TrinaryLogic|null $expected
	 */
	public function testCompareTo(TrinaryLogic $first, TrinaryLogic $second, ?TrinaryLogic $expected): void
	{
		self::assertSame(
			$expected,
			$first->compareTo($second)
		);
	}

	/**
	 * @dataProvider dataCompareTo
	 * @param TrinaryLogic $first
	 * @param TrinaryLogic $second
	 * @param TrinaryLogic|null $expected
	 */
	public function testCompareToInversed(TrinaryLogic $first, TrinaryLogic $second, ?TrinaryLogic $expected): void
	{
		self::assertSame(
			$expected,
			$second->compareTo($first)
		);
	}

}
