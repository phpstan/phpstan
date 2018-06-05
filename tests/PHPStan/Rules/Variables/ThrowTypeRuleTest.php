<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PHPStan\Rules\RuleLevelHelper;

class ThrowTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ThrowTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true), true);
	}

	public function testAccessPropertiesOnPossiblyNullRuleTest(): void
	{
		$this->analyse(
			[__DIR__ . '/data/throw-values.php'],
			[
				[
					'Invalid type int to throw.',
					19,
				],
				[
					'Possibly invalid type ThrowValues\InvalidException to throw.',
					20,
				],
				[
					'Possibly invalid type ThrowValues\InvalidInterfaceException to throw.',
					21,
				],
				[
					'Possibly invalid type Exception|null to throw.',
					22,
				],
			]
		);
	}

}
