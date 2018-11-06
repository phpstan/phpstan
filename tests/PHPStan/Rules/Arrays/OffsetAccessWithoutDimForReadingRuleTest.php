<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class OffsetAccessWithoutDimForReadingRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new OffsetAccessWithoutDimForReadingRule();
	}

	public function testOffsetAccessWithoutDimForReading(): void
	{
		$this->analyse(
			[__DIR__ . '/data/offset-access-without-dim-for-reading.php'],
			[
				[
					'Cannot use [] for reading.',
					7,
				],
				[
					'Cannot use [] for reading.',
					8,
				],
				[
					'Cannot use [] for reading.',
					9,
				],
				[
					'Cannot use [] for reading.',
					12,
				],
				[
					'Cannot use [] for reading.',
					13,
				],
				[
					'Cannot use [] for reading.',
					14,
				],
				[
					'Cannot use [] for reading.',
					14,
				],
				[
					'Cannot use [] for reading.',
					17,
				],
				[
					'Cannot use [] for reading.',
					21,
				],
				[
					'Cannot use [] for reading.',
					22,
				],
				[
					'Cannot use [] for reading.',
					23,
				],
				[
					'Cannot use [] for reading.',
					24,
				],
				[
					'Cannot use [] for reading.',
					27,
				],
				[
					'Cannot use [] for reading.',
					28,
				],
				[
					'Cannot use [] for reading.',
					29,
				],
				[
					'Cannot use [] for reading.',
					30,
				],
			]
		);
	}

}
