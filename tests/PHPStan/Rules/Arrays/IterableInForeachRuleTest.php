<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class IterableInForeachRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $reportMaybes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IterableInForeachRule($this->reportMaybes);
	}

	public function testCheckWithoutMaybes(): void
	{
		$this->reportMaybes = false;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
		]);
	}

	public function testCheckWithMaybes(): void
	{
		$this->reportMaybes = true;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
			[
				'Argument of a possibly invalid type array<int, int>|false supplied for foreach, only iterables are supported.',
				17,
			],
		]);
	}

}
