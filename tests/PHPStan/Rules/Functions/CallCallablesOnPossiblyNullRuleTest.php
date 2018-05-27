<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class CallCallablesOnPossiblyNullRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallCallablesOnPossiblyNullRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/callable-nullable.php'], [
			[
				'Invoking possibly null value of type string|null.',
				4,
			],
			[
				'Invoking possibly null value of type int|null.',
				5,
			],
		]);
	}

}
