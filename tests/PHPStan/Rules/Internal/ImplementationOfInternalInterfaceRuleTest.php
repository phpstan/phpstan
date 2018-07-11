<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class ImplementationOfInternalInterfaceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/implementation-of-internal-interface-in-internal-path-definition.php',
		]);

		return new ImplementationOfInternalInterfaceRule($broker, $internalScopeHelper);
	}

	public function testImplementationOfInternalInterfacesInClasses(): void
	{
		require_once __DIR__ . '/data/implementation-of-internal-interface-in-internal-path-definition.php';
		require_once __DIR__ . '/data/implementation-of-internal-interface-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/implementation-of-internal-interface-in-classes.php'],
			[
				[
					'Class ImplementationOfInternalInterface\Foo5 implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable.',
					28,
				],
				[
					'Class ImplementationOfInternalInterface\Foo6 implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable.',
					33,
				],
				[
					'Class ImplementationOfInternalInterface\Foo6 implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable2.',
					33,
				],
			]
		);
	}

	public function testImplementationOfInternalInterfacesInAnonymousClasses(): void
	{
		require_once __DIR__ . '/data/implementation-of-internal-interface-in-internal-path-definition.php';
		require_once __DIR__ . '/data/implementation-of-internal-interface-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/implementation-of-internal-interface-in-anonymous-classes.php'],
			[
				[
					'Anonymous class implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable.',
					24,
				],
				[
					'Anonymous class implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable.',
					28,
				],
				[
					'Anonymous class implements internal interface ImplementationOfInternalInterfaceInExternalPath\InternalFooable2.',
					28,
				],
			]
		);
	}

}
