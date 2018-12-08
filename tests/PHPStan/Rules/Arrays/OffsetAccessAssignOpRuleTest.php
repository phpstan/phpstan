<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class OffsetAccessAssignOpRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkUnions;

	protected function getRule(): Rule
	{
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, $this->checkUnions);
		return new OffsetAccessAssignOpRule($ruleLevelHelper);
	}

	public function testRule(): void
	{
		$this->checkUnions = true;
		$this->analyse([__DIR__ . '/data/offset-access-assignop.php'], [
			[
				'Cannot assign offset \'foo\' to true.',
				16,
			],
			[
				'Cannot assign offset \'foo\' to false.',
				19,
			],
			[
				'Cannot assign offset \'foo\' to resource.',
				23,
			],
			[
				'Cannot assign offset \'foo\' to float.',
				26,
			],
			[
				'Cannot assign offset \'foo\' to array|int.',
				30,
			],
			[
				'Cannot assign offset \'foo\' to int.',
				33,
			],
		]);
	}

	public function testRuleWithoutUnions(): void
	{
		$this->checkUnions = false;
		$this->analyse([__DIR__ . '/data/offset-access-assignop.php'], [
			[
				'Cannot assign offset \'foo\' to true.',
				16,
			],
			[
				'Cannot assign offset \'foo\' to false.',
				19,
			],
			[
				'Cannot assign offset \'foo\' to resource.',
				23,
			],
			[
				'Cannot assign offset \'foo\' to float.',
				26,
			],
			[
				'Cannot assign offset \'foo\' to int.',
				33,
			],
		]);
	}

}
