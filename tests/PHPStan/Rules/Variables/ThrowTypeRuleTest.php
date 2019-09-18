<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PHPStan\Rules\RuleLevelHelper;

class ThrowTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ThrowTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/throw-values.php'],
			[
				[
					'Invalid type int to throw.',
					24,
				],
				[
					'Invalid type ThrowValues\InvalidException to throw.',
					27,
				],
				[
					'Invalid type ThrowValues\InvalidInterfaceException to throw.',
					30,
				],
				[
					'Invalid type Exception|null to throw.',
					33,
				],
				[
					'Throwing object of an unknown class ThrowValues\NonexistentClass.',
					39,
				],
			]
		);
	}

}
