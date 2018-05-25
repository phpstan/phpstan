<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

class AccessPropertiesOnPossiblyNullRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessPropertiesOnPossiblyNullRule();
	}

	public function testAccessPropertiesOnPossiblyNullRuleTest(): void
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
