<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class BooleanNotConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new BooleanNotConstantConditionRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/boolean-not.php'], [
			[
				'Negated boolean is always false.',
				13,
			],
			[
				'Negated boolean is always true.',
				18,
			],
		]);
	}

}
