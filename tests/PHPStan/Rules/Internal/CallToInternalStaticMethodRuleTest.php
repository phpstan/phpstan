<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PHPStan\Rules\RuleLevelHelper;

class CallToInternalStaticMethodRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, true);
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/call-to-internal-static-method-in-internal-path-definition.php',
		]);

		return new CallToInternalStaticMethodRule($broker, $ruleLevelHelper, $internalScopeHelper);
	}

	public function testInternalStaticMethodCall(): void
	{
		require_once __DIR__ . '/data/call-to-internal-static-method-in-internal-path-definition.php';
		require_once __DIR__ . '/data/call-to-internal-static-method-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/call-to-internal-static-method.php'],
			[
				[
					'Call to internal method internalFoo() of class CheckInternalStaticMethodCallInExternalPath\Foo.',
					14,
				],
				[
					'Call to internal method internalFoo2() of class CheckInternalStaticMethodCallInExternalPath\Foo.',
					16,
				],
				[
					'Call to method foo() of internal class CheckInternalStaticMethodCallInExternalPath\Foo.',
					17,
				],
				[
					'Call to method internalFoo() of internal class CheckInternalStaticMethodCallInExternalPath\Foo.',
					18,
				],
				[
					'Call to internal method internalFoo() of class CheckInternalStaticMethodCallInExternalPath\Foo.',
					18,
				],
				[
					'Call to method internalFoo2() of internal class CheckInternalStaticMethodCallInExternalPath\Foo.',
					19,
				],
				[
					'Call to internal method internalFoo2() of class CheckInternalStaticMethodCallInExternalPath\Foo.',
					19,
				],
			]
		);
	}

}
