<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\RuleLevelHelper;

class AccessPropertiesOnPossiblyNullRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessPropertiesOnPossiblyNullRule(new RuleLevelHelper($this->createBroker(), true, false, true), false);
	}

	public function testAccessPropertiesOnPossiblyNullRuleTest()
	{
		$this->analyse(
			[__DIR__ . '/data/possibly-nullable.php'],
			[
				[
					'Accessing property $foo on possibly null value of type DateTimeImmutable|null.',
					11,
				],
			]
		);
	}

}
