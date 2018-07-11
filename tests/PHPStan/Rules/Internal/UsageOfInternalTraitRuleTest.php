<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class UsageOfInternalTraitRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/usage-of-internal-trait-in-internal-path-definition.php',
		]);

		return new UsageOfInternalTraitRule($broker, $internalScopeHelper);
	}

	public function testUsageOfInternalTrait(): void
	{
		require_once __DIR__ . '/data/usage-of-internal-trait-in-internal-path-definition.php';
		require_once __DIR__ . '/data/usage-of-internal-trait-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/usage-of-internal-trait.php'],
			[
				[
					'Usage of internal trait UsageOfInternalTraitInExternalPath\FooTrait in class UsageOfInternalTrait\Foo3.',
					24,
				],
				[
					'Usage of internal trait UsageOfInternalTraitInExternalPath\FooTrait in class UsageOfInternalTrait\Foo4.',
					32,
				],
			]
		);
	}

}
