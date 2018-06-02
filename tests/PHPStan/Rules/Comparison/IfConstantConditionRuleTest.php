<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class IfConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IfConstantConditionRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/if-condition.php'], [
			[
				'If condition is always true.',
				40,
			],
			[
				'If condition is always false.',
				45,
			],
		]);
	}

}
