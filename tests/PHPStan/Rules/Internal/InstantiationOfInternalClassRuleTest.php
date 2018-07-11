<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PHPStan\Rules\RuleLevelHelper;

class InstantiationOfInternalClassRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, true);
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/instantiation-of-internal-class-in-internal-path-definition.php',
		]);

		return new InstantiationOfInternalClassRule($broker, $ruleLevelHelper, $internalScopeHelper);
	}

	public function testInstantiationOfInternalClass(): void
	{
		require_once __DIR__ . '/data/instantiation-of-internal-class-in-internal-path-definition.php';
		require_once __DIR__ . '/data/instantiation-of-internal-class-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/instantiation-of-internal-class.php'],
			[
				[
					'Instantiation of internal class InstantiationOfInternalClassInExternalPath\InternalFoo.',
					9,
				],
			]
		);
	}

}
