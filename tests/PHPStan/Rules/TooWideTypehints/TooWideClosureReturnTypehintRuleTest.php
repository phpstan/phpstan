<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class TooWideClosureReturnTypehintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TooWideClosureReturnTypehintRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/tooWideClosureReturnType.php'], [
			[
				'Anonymous function never returns string so it can be removed from the return typehint.',
				16,
			],
			[
				'Anonymous function never returns null so it can be removed from the return typehint.',
				20,
			],
		]);
	}

}
