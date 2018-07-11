<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PHPStan\Rules\RuleLevelHelper;

class AccessInternalStaticPropertyRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, true);
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/access-internal-static-property-in-internal-path-definition.php',
		]);

		return new AccessInternalStaticPropertyRule($broker, $ruleLevelHelper, $internalScopeHelper);
	}

	public function testAccessInternalStaticProperty(): void
	{
		require_once __DIR__ . '/data/access-internal-static-property-in-internal-path-definition.php';
		require_once __DIR__ . '/data/access-internal-static-property-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/access-internal-static-property.php'],
			[
				[
					'Access to internal static property $internalFoo of class AccessInternalStaticPropertyInExternalPath\Foo.',
					36,
				],
				[
					'Access to internal static property $internalFoo of class AccessInternalStaticPropertyInExternalPath\Foo.',
					37,
				],
				[
					'Access to internal static property $internalFoo of class AccessInternalStaticPropertyInExternalPath\Foo.',
					44,
				],
				[
					'Access to internal static property $internalFoo of class AccessInternalStaticPropertyInExternalPath\Foo.',
					45,
				],
				[
					'Access to internal static property $internalFooFromTrait of class AccessInternalStaticPropertyInExternalPath\FooTrait.',
					50,
				],
				[
					'Access to internal static property $internalFooFromTrait of class AccessInternalStaticPropertyInExternalPath\FooTrait.',
					51,
				],
				[
					'Access to internal static property $internalFooFromTrait of class AccessInternalStaticPropertyInExternalPath\Foo.',
					58,
				],
				[
					'Access to internal static property $internalFooFromTrait of class AccessInternalStaticPropertyInExternalPath\Foo.',
					59,
				],
			]
		);
	}

}
