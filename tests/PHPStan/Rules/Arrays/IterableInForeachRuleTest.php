<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class IterableInForeachRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkUnionTypes;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IterableInForeachRule($this->checkUnionTypes);
	}

	public function testCheckWithoutUnionTypes()
	{
		$this->checkUnionTypes = false;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
		]);
	}

	public function testCheckWithUnionTypes()
	{
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
			[
				'Argument of an invalid type array<int, int>|false supplied for foreach, only iterables are supported.',
				17,
			],
		]);
	}

}
