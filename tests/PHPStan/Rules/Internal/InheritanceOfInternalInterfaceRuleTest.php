<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class InheritanceOfInternalInterfaceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/inheritance-of-internal-interface-in-internal-path-definition.php',
		]);

		return new InheritanceOfInternalInterfaceRule($broker, $internalScopeHelper);
	}

	public function testInheritanceOfInternalInterfaces(): void
	{
		require_once __DIR__ . '/data/inheritance-of-internal-interface-in-internal-path-definition.php';
		require_once __DIR__ . '/data/inheritance-of-internal-interface-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/inheritance-of-internal-interface.php'],
			[
				[
					'Interface InheritanceOfInternalInterface\Foo5 extends internal interface InheritanceOfInternalInterfaceInExternalPath\InternalFooable.',
					28,
				],
				[
					'Interface InheritanceOfInternalInterface\Foo6 extends internal interface InheritanceOfInternalInterfaceInExternalPath\InternalFooable.',
					33,
				],
				[
					'Interface InheritanceOfInternalInterface\Foo6 extends internal interface InheritanceOfInternalInterfaceInExternalPath\InternalFooable2.',
					33,
				],
			]
		);
	}

}
