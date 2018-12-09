<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class PrintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new PrintRule(
			new RuleLevelHelper($this->createBroker(), true, false, true)
		);
	}

	public function testPrintRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/print.php'],
			[
				[
					'Parameter array() of print cannot be converted to string.',
					5,
				],
				[
					'Parameter stdClass of print cannot be converted to string.',
					7,
				],
				[
					'Parameter Closure(): mixed of print cannot be converted to string.',
					9,
				],
				[
					'Parameter array() of print cannot be converted to string.',
					13,
				],
				[
					'Parameter stdClass of print cannot be converted to string.',
					15,
				],
				[
					'Parameter Closure(): mixed of print cannot be converted to string.',
					17,
				],
				[
					'Parameter \'string\'|array(\'string\') of print cannot be converted to string.',
					21,
				],
			]
		);
	}

}
