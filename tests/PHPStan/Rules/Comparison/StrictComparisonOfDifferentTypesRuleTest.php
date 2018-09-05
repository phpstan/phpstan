<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class StrictComparisonOfDifferentTypesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkAlwaysTrueStrictComparison;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new StrictComparisonOfDifferentTypesRule($this->checkAlwaysTrueStrictComparison);
	}

	public function testStrictComparison(): void
	{
		$this->checkAlwaysTrueStrictComparison = true;
		$this->analyse(
			[__DIR__ . '/data/strict-comparison.php'],
			[
				[
					'Strict comparison using === between 1 and 1 will always evaluate to true.',
					10,
				],
				[
					'Strict comparison using === between 1 and \'1\' will always evaluate to false.',
					11,
				],
				[
					'Strict comparison using !== between 1 and \'1\' will always evaluate to true.',
					12,
				],
				[
					'Strict comparison using === between 1 and null will always evaluate to false.',
					14,
				],
				[
					'Strict comparison using === between StrictComparison\Bar and 1 will always evaluate to false.',
					15,
				],
				[
					'Strict comparison using === between 1 and array<StrictComparison\Foo>|bool|StrictComparison\Collection will always evaluate to false.',
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
					'Strict comparison using === between 1.0 and 1 will always evaluate to false.',
					46,
				],
				[
					'Strict comparison using === between 1 and 1.0 will always evaluate to false.',
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
					'Strict comparison using !== between StrictComparison\Foo|null and 1 will always evaluate to true.',
					88,
				],
				[
					'Strict comparison using === between 1|2|3 and null will always evaluate to false.',
					98,
				],
				[
					'Strict comparison using !== between StrictComparison\Foo|null and 1 will always evaluate to true.',
					130,
				],
				[
					'Strict comparison using === between array and null will always evaluate to false.',
					140,
				],
				[
					'Strict comparison using !== between StrictComparison\Foo|null and 1 will always evaluate to true.',
					154,
				],
				[
					'Strict comparison using === between array and null will always evaluate to false.',
					164,
				],
				[
					'Strict comparison using !== between StrictComparison\Node|null and false will always evaluate to true.',
					212,
				],
				[
					'Strict comparison using !== between StrictComparison\Node|null and false will always evaluate to true.',
					255,
				],
				[
					'Strict comparison using !== between stdClass and null will always evaluate to true.',
					271,
				],
				[
					'Strict comparison using === between 1 and 2 will always evaluate to false.',
					284,
				],
				[
					'Strict comparison using === between array(\'X\' => 1) and array(\'X\' => 2) will always evaluate to false.',
					292,
				],
				[
					'Strict comparison using === between array(\'X\' => 1, \'Y\' => 2) and array(\'X\' => 2, \'Y\' => 1) will always evaluate to false.',
					300,
				],
				[
					'Strict comparison using === between array(\'X\' => 1, \'Y\' => 2) and array(\'Y\' => 2, \'X\' => 1) will always evaluate to false.',
					308,
				],
				[
					'Strict comparison using === between \'/\'|\'\\\\\' and \'//\' will always evaluate to false.',
					320,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					335,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					343,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					360,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					368,
				],
				[
					'Strict comparison using === between float and \'string\' will always evaluate to false.',
					386,
				],
				[
					'Strict comparison using === between float and \'string\' will always evaluate to false.',
					394,
				],
				[
					'Strict comparison using !== between null and null will always evaluate to false.',
					408,
				],
				[
					'Strict comparison using === between 0 and 0 will always evaluate to true.',
					426,
				],
				[
					'Strict comparison using === between int|string and 1.0 will always evaluate to false.',
					464,
				],
				[
					'Strict comparison using === between int|string and stdClass will always evaluate to false.',
					466,
				],
				[
					'Strict comparison using === between int and \'foo\' will always evaluate to false.',
					624,
				],
				[
					'Strict comparison using === between int and \'foo\' will always evaluate to false.',
					635,
				],
				[
					'Strict comparison using === between \'foofoofoofoofoofoof…\' and \'foofoofoofoofoofoof…\' will always evaluate to true.',
					654,
				],
			]
		);
	}

	public function testStrictComparisonWithoutAlwaysTrue(): void
	{
		$this->checkAlwaysTrueStrictComparison = false;
		$this->analyse(
			[__DIR__ . '/data/strict-comparison.php'],
			[
				[
					'Strict comparison using === between 1 and \'1\' will always evaluate to false.',
					11,
				],
				[
					'Strict comparison using === between 1 and null will always evaluate to false.',
					14,
				],
				[
					'Strict comparison using === between StrictComparison\Bar and 1 will always evaluate to false.',
					15,
				],
				[
					'Strict comparison using === between 1 and array<StrictComparison\Foo>|bool|StrictComparison\Collection will always evaluate to false.',
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
					'Strict comparison using === between 1.0 and 1 will always evaluate to false.',
					46,
				],
				[
					'Strict comparison using === between 1 and 1.0 will always evaluate to false.',
					47,
				],
				[
					'Strict comparison using === between string and null will always evaluate to false.',
					69,
				],
				[
					'Strict comparison using === between 1|2|3 and null will always evaluate to false.',
					98,
				],
				[
					'Strict comparison using === between array and null will always evaluate to false.',
					140,
				],
				[
					'Strict comparison using === between array and null will always evaluate to false.',
					164,
				],
				[
					'Strict comparison using === between 1 and 2 will always evaluate to false.',
					284,
				],
				[
					'Strict comparison using === between array(\'X\' => 1) and array(\'X\' => 2) will always evaluate to false.',
					292,
				],
				[
					'Strict comparison using === between array(\'X\' => 1, \'Y\' => 2) and array(\'X\' => 2, \'Y\' => 1) will always evaluate to false.',
					300,
				],
				[
					'Strict comparison using === between array(\'X\' => 1, \'Y\' => 2) and array(\'Y\' => 2, \'X\' => 1) will always evaluate to false.',
					308,
				],
				[
					'Strict comparison using === between \'/\'|\'\\\\\' and \'//\' will always evaluate to false.',
					320,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					335,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					343,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					360,
				],
				[
					'Strict comparison using === between int and \'string\' will always evaluate to false.',
					368,
				],
				[
					'Strict comparison using === between float and \'string\' will always evaluate to false.',
					386,
				],
				[
					'Strict comparison using === between float and \'string\' will always evaluate to false.',
					394,
				],
				[
					'Strict comparison using !== between null and null will always evaluate to false.',
					408,
				],
				[
					'Strict comparison using === between int|string and 1.0 will always evaluate to false.',
					464,
				],
				[
					'Strict comparison using === between int|string and stdClass will always evaluate to false.',
					466,
				],
				[
					'Strict comparison using === between int and \'foo\' will always evaluate to false.',
					624,
				],
				[
					'Strict comparison using === between int and \'foo\' will always evaluate to false.',
					635,
				],
			]
		);
	}

	public function testStrictComparisonPhp71(): void
	{
		$this->checkAlwaysTrueStrictComparison = true;
		$this->analyse([__DIR__ . '/data/strict-comparison-71.php'], [
			[
				'Strict comparison using === between null and null will always evaluate to true.',
				20,
			],
		]);
	}

}
