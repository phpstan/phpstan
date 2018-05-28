<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\RuleLevelHelper;

class IterableInForeachRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IterableInForeachRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testCheckWithMaybes(): void
	{
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				10,
			],
			[
				'Argument of an invalid type array<int, int>|false supplied for foreach, only iterables are supported.',
				19,
			],
			[
				'Iterating over an object of an unknown class IterablesInForeach\Bar.',
				46,
			],
		]);
	}

}
