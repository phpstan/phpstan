<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arithmetic;

class InvalidArithmeticOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidArithmeticOperationRule(new \PhpParser\PrettyPrinter\Standard());
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-arithmetic.php'], [
			[
				'Arithmetic operation "-" between array and array results in an error.',
				12,
			],
			[
				'Arithmetic operation "/" between int(5) and int(0) results in an error.',
				15,
			],
			[
				'Arithmetic operation "%" between int(5) and int(0) results in an error.',
				16,
			],
			[
				'Arithmetic operation "/" between int and float(0.000000) results in an error.',
				17,
			],
			[
				'Arithmetic operation "+" between mixed and array results in an error.',
				19,
			],
			[
				'Arithmetic operation "+" between int(1) and string results in an error.',
				20,
			],
			[
				'Arithmetic operation "+" between int(1) and string results in an error.',
				21,
			],
			[
				'Arithmetic operation "+=" between array and string results in an error.',
				28,
			],
			[
				'Arithmetic operation "-=" between array and array results in an error.',
				34,
			],
			[
				'Arithmetic operation "<<" between string and string results in an error.',
				47,
			],
			[
				'Arithmetic operation ">>" between string and string results in an error.',
				48,
			],
			[
				'Arithmetic operation ">>=" between string and string results in an error.',
				49,
			],
			[
				'Arithmetic operation "<<=" between string and string results in an error.',
				59,
			],
		]);
	}

}
