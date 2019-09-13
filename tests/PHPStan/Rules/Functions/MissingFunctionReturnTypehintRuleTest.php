<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class MissingFunctionReturnTypehintRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MissingFunctionReturnTypehintRule($this->createBroker([], []));
	}

	public function testRule(): void
	{
		require_once __DIR__ . '/data/missing-function-return-typehint.php';
		$this->analyse([__DIR__ . '/data/missing-function-return-typehint.php'], [
			[
				'Function globalFunction1() has no return typehint specified.',
				5,
			],
			[
				'Function MissingFunctionReturnTypehint\namespacedFunction1() has no return typehint specified.',
				30,
			],
		]);
	}

}
