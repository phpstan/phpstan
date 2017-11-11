<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class StrictComparisonOfDifferentTypesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new StrictComparisonOfDifferentTypesRule();
	}

	public function testStrictComparison()
	{
		$this->analyse(
			[__DIR__ . '/data/strict-comparison.php'],
			[
				[
					'Strict comparison using === between int and string will always evaluate to false.',
					11,
				],
				[
					'Strict comparison using !== between int and string will always evaluate to true.',
					12,
				],
				[
					'Strict comparison using === between int and null will always evaluate to false.',
					14,
				],
				[
					'Strict comparison using === between StrictComparison\Bar and int will always evaluate to false.',
					15,
				],
				[
					'Strict comparison using === between int and bool|StrictComparison\Collection|StrictComparison\Foo[] will always evaluate to false.',
					19,
				],
				[
					'Strict comparison using === between true and false will always evaluate to false.',
					30,
				],
				[
					'Strict comparison using === between false and true will always evaluate to false.',
					31,
				],
				[
					'Strict comparison using === between float and int will always evaluate to false.',
					46,
				],
				[
					'Strict comparison using === between int and float will always evaluate to false.',
					47,
				],
				[
					'Strict comparison using === between string and null will always evaluate to false.',
					69,
				],
				[
					'Strict comparison using !== between string and null will always evaluate to true.',
					76,
				],
				[
					'Strict comparison using !== between StrictComparison\Foo|null and int will always evaluate to true.',
					88,
				],
				[
					'Strict comparison using === between int and null will always evaluate to false.',
					98,
				],
				[
					'Strict comparison using !== between StrictComparison\Foo|null and int will always evaluate to true.',
					130,
				],
				[
					'Strict comparison using === between mixed[] and null will always evaluate to false.',
					140,
				],
			]
		);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testStrictComparisonPhp71()
	{
		$this->analyse([__DIR__ . '/data/strict-comparison-71.php'], []);
	}

}
