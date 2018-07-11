<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class CallToInternalFunctionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/call-to-internal-function-in-internal-path-definition.php',
		]);

		return new CallToInternalFunctionRule($broker, $internalScopeHelper);
	}

	public function testInternalFunctionCall(): void
	{
		require_once __DIR__ . '/data/call-to-internal-function-in-internal-path-definition.php';
		require_once __DIR__ . '/data/call-to-internal-function-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/call-to-internal-function.php'],
			[
				[
					'Call to internal function CheckInternalFunctionCallInExternalPath\internal_foo().',
					9,
				],
			]
		);
	}

}
