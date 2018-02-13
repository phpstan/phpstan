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

	public function testCheckWithoutUnionTypes(): void
	{
		$this->checkUnionTypes = false;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
		]);
	}

	public function testCheckWithUnionTypes(): void
	{
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
			[
				'Argument of an invalid type array<int(0)|int(1)|int(2), int(1)|int(2)|int(3)>|false supplied for foreach, only iterables are supported.',
				17,
			],
		]);
	}

}
