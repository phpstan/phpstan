<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class AccessInternalPropertyRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/access-internal-property-in-internal-path-definition.php',
		]);

		return new AccessInternalPropertyRule($broker, $internalScopeHelper);
	}

	public function testAccessInternalProperty(): void
	{
		require_once __DIR__ . '/data/access-internal-property-in-internal-path-definition.php';
		require_once __DIR__ . '/data/access-internal-property-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/access-internal-property.php'],
			[
				[
					'Access to internal property $internalFoo of class AccessInternalPropertyInExternalPath\Foo.',
					24,
				],
				[
					'Access to internal property $internalFoo of class AccessInternalPropertyInExternalPath\Foo.',
					25,
				],
				[
					'Access to internal property $internalFooFromTrait of class AccessInternalPropertyInExternalPath\Foo.',
					30,
				],
				[
					'Access to internal property $internalFooFromTrait of class AccessInternalPropertyInExternalPath\Foo.',
					31,
				],
			]
		);
	}

}
