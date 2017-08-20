<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class IterableInForeachRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IterableInForeachRule();
	}

	public function testInvalidKey()
	{
		$this->analyse([__DIR__ . '/data/foreach-iterable.php'], [
			[
				'Argument of an invalid type string supplied for foreach, only iterables are supported.',
				8,
			],
		]);
	}

}
