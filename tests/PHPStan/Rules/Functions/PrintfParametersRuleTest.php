<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class PrintfParametersRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new PrintfParametersRule();
	}

	public function testFile()
	{
		$this->analyse([__DIR__ . '/data/printf.php'], [
			[
				'Call to sprintf contains 2 placeholders, 1 value given.',
				6,
			],
			[
				'Call to sprintf contains 0 placeholders, 1 value given.',
				7,
			],
			[
				'Call to sprintf contains 1 placeholder, 2 values given.',
				8,
			],
			[
				'Call to sprintf contains 2 placeholders, 1 value given.',
				9,
			],
			[
				'Call to sprintf contains 2 placeholders, 0 values given.',
				10,
			],
			[
				'Call to sprintf contains 4 placeholders, 0 values given.',
				11,
			],
			[
				'Call to sprintf contains 5 placeholders, 2 values given.',
				13,
			],
			[
				'Call to sprintf contains 1 placeholder, 2 values given.',
				15,
			],
			[
				'Call to sprintf contains 6 placeholders, 0 values given.',
				16,
			],
			[
				'Call to sprintf contains 2 placeholders, 0 values given.',
				17,
			],
			[
				'Call to sprintf contains 1 placeholder, 0 values given.',
				18,
			],
			[
				'Call to sscanf contains 2 placeholders, 1 value given.',
				21,
			],
			[
				'Call to fscanf contains 2 placeholders, 1 value given.',
				25,
			],
			[
				'Call to sprintf contains 2 placeholders, 1 value given.',
				27,
			],
			[
				'Call to sprintf contains 2 placeholders, 1 value given.',
				29,
			],
		]);
	}

}
