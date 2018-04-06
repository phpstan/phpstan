<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

class InvalidBinaryOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidBinaryOperationRule(new \PhpParser\PrettyPrinter\Standard());
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-binary.php'], [
			[
				'Binary operation "-" between array and array results in an error.',
				12,
			],
			[
				'Binary operation "/" between int(5) and int(0) results in an error.',
				15,
			],
			[
				'Binary operation "%" between int(5) and int(0) results in an error.',
				16,
			],
			[
				'Binary operation "/" between int and float(0.000000) results in an error.',
				17,
			],
			[
				'Binary operation "+" between mixed and array results in an error.',
				19,
			],
			[
				'Binary operation "+" between int(1) and string results in an error.',
				20,
			],
			[
				'Binary operation "+" between int(1) and string results in an error.',
				21,
			],
			[
				'Binary operation "+=" between array and string results in an error.',
				28,
			],
			[
				'Binary operation "-=" between array and array results in an error.',
				34,
			],
			[
				'Binary operation "<<" between string and string results in an error.',
				47,
			],
			[
				'Binary operation ">>" between string and string results in an error.',
				48,
			],
			[
				'Binary operation ">>=" between string and string results in an error.',
				49,
			],
			[
				'Binary operation "<<=" between string and string results in an error.',
				59,
			],
			[
				'Binary operation "&" between string and int(5) results in an error.',
				67,
			],
			[
				'Binary operation "|" between string and int(5) results in an error.',
				69,
			],
			[
				'Binary operation "^" between string and int(5) results in an error.',
				71,
			],
		]);
	}

}
