<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\Rules\RuleLevelHelper;

class AppendedArrayItemToStringRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AppendedArrayItemToStringRule(
			new RuleLevelHelper($this->createBroker(), true, false, true)
		);
	}

	public function testAppendedArrayItemType(): void
	{
		$this->analyse(
			[__DIR__ . '/data/appended-array-item-to-string.php'],
			[
				[
					'Append array to variable of invalid type string',
					14,
				],
			]
		);
	}
}
