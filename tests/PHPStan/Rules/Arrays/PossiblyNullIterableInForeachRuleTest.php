<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class PossiblyNullIterableInForeachRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new PossiblyNullIterableInForeachRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/foreach-iterable-null.php'], [
			[
				'Iterating over a possibly null value of type int|null.',
				6,
			],
			[
				'Iterating over a possibly null value of type array|null.',
				9,
			],
		]);
	}

}
