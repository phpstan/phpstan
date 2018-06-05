<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PHPStan\Rules\RuleLevelHelper;

class InvalidBinaryOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidBinaryOperationRule(
			new \PhpParser\PrettyPrinter\Standard(),
			new RuleLevelHelper($this->createBroker(), true, false, true)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-binary.php'], [
			[
				'Binary operation "-" between array and array results in an error.',
				12,
			],
			[
				'Binary operation "/" between 5 and 0 results in an error.',
				15,
			],
			[
				'Binary operation "%" between 5 and 0 results in an error.',
				16,
			],
			[
				'Binary operation "/" between int and 0.0 results in an error.',
				17,
			],
			[
				'Binary operation "+" between 1 and string results in an error.',
				20,
			],
			[
				'Binary operation "+" between 1 and \'blabla\' results in an error.',
				21,
			],
			[
				'Binary operation "+=" between array and \'foo\' results in an error.',
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
				'Binary operation "&" between string and 5 results in an error.',
				67,
			],
			[
				'Binary operation "|" between string and 5 results in an error.',
				69,
			],
			[
				'Binary operation "^" between string and 5 results in an error.',
				71,
			],
			[
				'Binary operation "." between string and stdClass results in an error.',
				81,
			],
			[
				'Binary operation ".=" between string and stdClass results in an error.',
				85,
			],
			[
				'Binary operation "/" between 5 and 0|1 results in an error.',
				116,
			],
			[
				'Binary operation "." between array and \'xyz\' results in an error.',
				121,
			],
			[
				'Binary operation "." between array|string and \'xyz\' results in an error.',
				128,
			],
			[
				'Binary operation "+" between (array|string) and 1 results in an error.',
				130,
			],
		]);
	}

}
