<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class CallCallablesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallCallablesRule(true);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/callables.php'], [
			[
				'Trying to invoke string but it might not be a callable.',
				17,
			],
			[
				'Trying to invoke \'nonexistent\' but it\'s not a callable.',
				24,
			],
		]);
	}

}
