<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCertaintyInIssetRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new VariableCertaintyInIssetRule();
	}

	public function testVariableCertaintyInIsset(): void
	{
		$this->analyse([__DIR__ . '/data/variable-certainty-isset.php'], [
			[
				'Variable $alwaysDefinedNotNullable in isset() always exists and is not nullable.',
				14,
			],
			[
				'Variable $neverDefinedVariable in isset() is never defined.',
				22,
			],
			[
				'Variable $anotherNeverDefinedVariable in isset() is never defined.',
				42,
			],
			[
				'Variable $yetAnotherNeverDefinedVariable in isset() is never defined.',
				46,
			],
			[
				'Variable $yetYetAnotherNeverDefinedVariableInIsset in isset() is never defined.',
				56,
			],
			[
				'Variable $anotherVariableInDoWhile in isset() always exists and is not nullable.',
				104,
			],
			[
				'Variable $variableInSecondCase in isset() is never defined.',
				110,
			],
			[
				'Variable $variableInFirstCase in isset() always exists and is not nullable.',
				112,
			],
			[
				'Variable $variableInFirstCase in isset() always exists and is not nullable.',
				116,
			],
			[
				'Variable $variableInSecondCase in isset() always exists and is not nullable.',
				117,
			],
			[
				'Variable $variableAssignedInSecondCase in isset() is never defined.',
				119,
			],
			[
				'Variable $alwaysDefinedForSwitchCondition in isset() always exists and is not nullable.',
				139,
			],
			[
				'Variable $alwaysDefinedForCaseNodeCondition in isset() always exists and is not nullable.',
				140,
			],
			[
				'Variable $alwaysDefinedNotNullable in isset() always exists and is not nullable.',
				152,
			],
			[
				'Variable $neverDefinedVariable in isset() is never defined.',
				152,
			],
			[
				'Variable $a in isset() always exists and is not nullable.',
				214,
			],
		]);
	}

	public function testIssetInGlobalScope(): void
	{
		$this->analyse([__DIR__ . '/data/isset-global-scope.php'], [
			[
				'Variable $alwaysDefinedNotNullable in isset() always exists and is not nullable.',
				8,
			],
		]);
	}

}
