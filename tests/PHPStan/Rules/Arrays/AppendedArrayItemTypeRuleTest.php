<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class AppendedArrayItemTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AppendedArrayItemTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testAppendedArrayItemType()
	{
		$this->analyse(
			[__DIR__ . '/data/appended-array-item.php'],
			[
				[
					'Array (int[]) does not accept string.',
					28,
				],
				[
					'Array (callable[]) does not accept int[].',
					32,
				],
			]
		);
	}

}
