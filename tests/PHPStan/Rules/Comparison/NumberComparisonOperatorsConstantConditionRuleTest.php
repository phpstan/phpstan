<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class NumberComparisonOperatorsConstantConditionRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new NumberComparisonOperatorsConstantConditionRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/number-comparison-operators.php'], [
			[
				'Comparison operation "<=" between int<6, max> and 2 is always false.',
				7,
			],
			[
				'Comparison operation ">" between int<2, 4> and 8 is always false.',
				13,
			],
			[
				'Comparison operation "<" between int<min, 1> and 5 is always true.',
				21,
			],
		]);
	}

}
