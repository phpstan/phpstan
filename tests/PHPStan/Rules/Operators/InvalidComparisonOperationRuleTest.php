<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PHPStan\Rules\RuleLevelHelper;

class InvalidComparisonOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidComparisonOperationRule(
			new RuleLevelHelper($this->createBroker(), true, false, true)
		);
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/invalid-comparison.php'],
			[
				[
					'Comparison operation "==" between stdClass and int results in an error.',
					15,
				],
				[
					'Comparison operation "!=" between stdClass and int results in an error.',
					16,
				],
				[
					'Comparison operation "<" between stdClass and int results in an error.',
					17,
				],
				[
					'Comparison operation ">" between stdClass and int results in an error.',
					18,
				],
				[
					'Comparison operation "<=" between stdClass and int results in an error.',
					19,
				],
				[
					'Comparison operation ">=" between stdClass and int results in an error.',
					20,
				],
				[
					'Comparison operation "<=>" between stdClass and int results in an error.',
					21,
				],
				[
					'Comparison operation "==" between stdClass and float|null results in an error.',
					25,
				],
				[
					'Comparison operation "<" between stdClass and float|null results in an error.',
					26,
				],
				[
					'Comparison operation "==" between stdClass and float|int|null results in an error.',
					43,
				],
				[
					'Comparison operation "<" between stdClass and float|int|null results in an error.',
					44,
				],
				[
					'Comparison operation "==" between stdClass and 1 results in an error.',
					48,
				],
				[
					'Comparison operation "<" between stdClass and 1 results in an error.',
					49,
				],
				[
					'Comparison operation "==" between stdClass and int|stdClass results in an error.',
					56,
				],
				[
					'Comparison operation "<" between stdClass and int|stdClass results in an error.',
					57,
				],
			]
		);
	}

}
