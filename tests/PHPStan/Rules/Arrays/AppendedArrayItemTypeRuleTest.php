<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class AppendedArrayItemTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AppendedArrayItemTypeRule();
	}

	public function testAppendedArrayItemType()
	{
		$this->analyse(
			[__DIR__ . '/data/appended-array-item.php'],
			[
				[
					'Array (int[]) does not accept string.',
					7,
				],
				[
					'Array (int[]) does not accept string.',
					8,
				],
			]
		);
	}

}
