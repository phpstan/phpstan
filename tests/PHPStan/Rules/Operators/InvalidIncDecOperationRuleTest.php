<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

class InvalidIncDecOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidIncDecOperationRule(false);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-inc-dec.php'], [
			[
				'Cannot use ++ on a non-variable.',
				11,
			],
			[
				'Cannot use -- on a non-variable.',
				12,
			],
			[
				'Cannot use ++ on stdClass.',
				17,
			],
		]);
	}

}
