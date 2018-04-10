<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class AppendedArrayItemTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AppendedArrayItemTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testAppendedArrayItemType(): void
	{
		$this->analyse(
			[__DIR__ . '/data/appended-array-item.php'],
			[
				[
					'Array (array<int>) does not accept string.',
					28,
				],
				[
					'Array (array<int|string>) does not accept string.',
					29,
				],
				[
					'Array (array<callable>) does not accept array(int(1), int(2), int(3)).',
					32,
				],
				[
					'Array (array<callable>) does not accept array(string, string).',
					54,
				],
			]
		);
	}

}
