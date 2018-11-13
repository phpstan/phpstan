<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class NonexistentOffsetInArrayDimFetchRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new NonexistentOffsetInArrayDimFetchRule(
			new RuleLevelHelper($this->createBroker(), true, false, true),
			true
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/nonexistent-offset.php'], [
			[
				'Offset \'b\' does not exist on array(\'a\' => stdClass, 0 => 2).',
				17,
			],
			[
				'Offset 1 does not exist on array(\'a\' => stdClass, 0 => 2).',
				18,
			],
			[
				'Offset \'a\' does not exist on array(\'b\' => 1).',
				55,
			],
			[
				'Access to offset \'bar\' on an unknown class NonexistentOffset\Bar.',
				101,
			],
			[
				'Access to an offset on an unknown class NonexistentOffset\Bar.',
				102,
			],
			[
				'Offset 0 does not exist on array<string, string>.',
				111,
			],
			[
				'Offset int does not exist on array<string, string>.',
				114,
			],
			[
				'Offset \'c\' does not exist on array(\'c\' => bool)|array(\'e\' => true).',
				171,
			],
			[
				'Offset int does not exist on array()|array(1 => 1, 2 => 2)|array(3 => 3, 4 => 4).',
				190,
			],
			[
				'Offset int does not exist on array()|array(1 => 1, 2 => 2)|array(3 => 3, 4 => 4).',
				193,
			],
			[
				'Offset \'b\' does not exist on array(\'a\' => \'blabla\').',
				225,
			],
			[
				'Offset \'b\' does not exist on array(\'a\' => \'blabla\').',
				228,
			],
			[
				'Offset string does not exist on array<int, mixed>.',
				240,
			],
			[
				'Cannot access offset \'a\' on Closure(): mixed.',
				253,
			],
			[
				'Offset \'a\' does not exist on array(1 => \'\').',
				284,
			],
			[
				'Offset 1 does not exist on array<string, \'\'>.',
				290,
			],
			[
				'Offset \'a\' does not exist on array(1 => \'\').',
				294,
			],
			[
				'Offset \'1\' does not exist on array(\'a\' => \'\').',
				298,
			],
			[
				'Offset 1 does not exist on array(\'a\' => \'\').',
				300,
			],
			[
				'Offset 1 does not exist on array<string, string>.',
				316,
			],
			[
				'Offset \'a\' does not exist on array<int, string>.',
				319,
			],
		]);
	}

	public function testStrings(): void
	{
		$this->analyse([__DIR__ . '/data/strings-offset-access.php'], [
			[
				'Offset \'foo\' does not exist on \'foo\'.',
				10,
			],
			[
				'Offset 12.34 does not exist on \'foo\'.',
				13,
			],
			[
				'Offset int|object does not exist on \'foo\'.',
				16,
			],
			[
				'Offset \'foo\' does not exist on array|string.',
				24,
			],
			[
				'Offset 12.34 does not exist on array|string.',
				28,
			],
			[
				'Offset int|object does not exist on array|string.',
				32,
			],
		]);
	}

	public function testAssignOp(): void
	{
		$this->analyse([__DIR__ . '/data/offset-access-assignop.php'], [
			[
				'Offset \'foo\' does not exist on array().',
				4,
			],
			[
				'Offset \'foo\' does not exist on \'Foo\'.',
				10,
			],
			[
				'Cannot access offset \'foo\' on stdClass.',
				13,
			],
		]);
	}

}
