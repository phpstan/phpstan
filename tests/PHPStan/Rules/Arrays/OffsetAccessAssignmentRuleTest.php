<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class OffsetAccessAssignmentRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkUnionTypes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, $this->checkUnionTypes);
		return new OffsetAccessAssignmentRule($ruleLevelHelper);
	}

	public function testOffsetAccessAssignmentToScalar(): void
	{
		$this->checkUnionTypes = true;
		$this->analyse(
			[__DIR__ . '/data/offset-access-assignment-to-scalar.php'],
			[
				[
					'Cannot assign offset \'foo\' to string.',
					7,
				],
				[
					'Cannot assign new offset to string.',
					10,
				],
				[
					'Cannot assign offset 12.34 to string.',
					13,
				],
				[
					'Cannot assign offset \'foo\' to array|string.',
					21,
				],
				[
					'Cannot assign offset int|object to array|string.',
					28,
				],
				[
					'Cannot assign offset int|object to string.',
					31,
				],
				[
					'Cannot assign offset \'foo\' to stdClass.',
					34,
				],
				[
					'Cannot assign offset \'foo\' to true.',
					37,
				],
				[
					'Cannot assign offset \'foo\' to false.',
					40,
				],
				[
					'Cannot assign offset \'foo\' to resource.',
					44,
				],
				[
					'Cannot assign offset \'foo\' to int.',
					47,
				],
				[
					'Cannot assign offset \'foo\' to float.',
					50,
				],
				[
					'Cannot assign offset \'foo\' to array|int.',
					54,
				],
			]
		);
	}

	public function testOffsetAccessAssignmentToScalarWithoutMaybes(): void
	{
		$this->checkUnionTypes = false;
		$this->analyse(
			[__DIR__ . '/data/offset-access-assignment-to-scalar.php'],
			[
				[
					'Cannot assign offset \'foo\' to string.',
					7,
				],
				[
					'Cannot assign new offset to string.',
					10,
				],
				[
					'Cannot assign offset 12.34 to string.',
					13,
				],
				[
					'Cannot assign offset \'foo\' to stdClass.',
					34,
				],
				[
					'Cannot assign offset \'foo\' to true.',
					37,
				],
				[
					'Cannot assign offset \'foo\' to false.',
					40,
				],
				[
					'Cannot assign offset \'foo\' to resource.',
					44,
				],
				[
					'Cannot assign offset \'foo\' to int.',
					47,
				],
				[
					'Cannot assign offset \'foo\' to float.',
					50,
				],
			]
		);
	}

}
