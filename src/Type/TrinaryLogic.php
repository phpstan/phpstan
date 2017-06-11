<?php declare(strict_types = 1);

namespace PHPStan\Type;

/**
 * @see https://en.wikipedia.org/wiki/Three-valued_logic
 */
class TrinaryLogic
{

	const YES = 1;
	const MAYBE = 0;
	const NO = -1;

	public static function and(int ...$operands): int
	{
		return min($operands);
	}

	public static function or(int ...$operands): int
	{
		return max($operands);
	}

	public static function not(int $operand): int
	{
		return -$operand;
	}

}
