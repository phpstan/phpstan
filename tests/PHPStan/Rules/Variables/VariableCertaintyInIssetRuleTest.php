<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCertaintyInIssetRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new VariableCertaintyInIssetRule();
	}

	public function testVariableCertaintyInIsset()
	{
		$this->analyse([__DIR__ . '/data/variable-certainty-isset.php'], [
			[
				'Variable $alwaysDefinedNotNullable in isset() always exists and is not nullable.',
				11,
			],
			[
				'Variable $neverDefinedVariable in isset() is never defined.',
				19,
			],
			[
				'Variable $anotherNeverDefinedVariable in isset() is never defined.',
				39,
			],
			[
				'Variable $yetAnotherNeverDefinedVariable in isset() is never defined.',
				43,
			],
			[
				'Variable $yetYetAnotherNeverDefinedVariableInIsset in isset() is never defined.',
				53,
			],
			[
				'Variable $anotherVariableInDoWhile in isset() always exists and is not nullable.',
				101,
			],
			[
				'Variable $variableInSecondCase in isset() is never defined.',
				107,
			],
			[
				'Variable $variableInFirstCase in isset() always exists and is not nullable.',
				109,
			],
			[
				'Variable $variableInFirstCase in isset() always exists and is not nullable.',
				113,
			],
			[
				'Variable $variableInSecondCase in isset() always exists and is not nullable.',
				114,
			],
			[
				'Variable $variableAssignedInSecondCase in isset() is never defined.',
				116,
			],
			[
				'Variable $alwaysDefinedForSwitchCondition in isset() always exists and is not nullable.',
				136,
			],
			[
				'Variable $alwaysDefinedForCaseNodeCondition in isset() always exists and is not nullable.',
				137,
			],
		]);
	}

}
