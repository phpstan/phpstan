<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class CallToInternalMethodRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/call-to-internal-method-in-internal-path-definition.php',
		]);

		return new CallToInternalMethodRule($broker, $internalScopeHelper);
	}

	public function testInternalMethodCall(): void
	{
		require_once __DIR__ . '/data/call-to-internal-method-in-internal-path-definition.php';
		require_once __DIR__ . '/data/call-to-internal-method-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/call-to-internal-method.php'],
			[
				[
					'Call to internal method internalFoo() of class CheckInternalMethodCallInExternalPath\Foo.',
					18,
				],
				[
					'Call to internal method internalFoo2() of class CheckInternalMethodCallInExternalPath\Foo.',
					22,
				],
				[
					'Call to internal method internalFooFromTrait() of class CheckInternalMethodCallInExternalPath\Foo.',
					25,
				],
			]
		);
	}

}
