<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class TooWideFunctionReturnTypehintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TooWideFunctionReturnTypehintRule();
	}

	public function testRule(): void
	{
		require_once __DIR__ . '/data/tooWideFunctionReturnType.php';
		$this->analyse([__DIR__ . '/data/tooWideFunctionReturnType.php'], [
			[
				'Function TooWideFunctionReturnType\bar() never returns string so it can be removed from the return typehint.',
				11,
			],
			[
				'Function TooWideFunctionReturnType\baz() never returns null so it can be removed from the return typehint.',
				15,
			],
		]);
	}

}
