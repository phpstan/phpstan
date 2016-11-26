<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

class UselessCastRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new UselessCastRule();
	}

	public function testUselessCast()
	{
		$this->analyse(
			[__DIR__ . '/data/useless-cast.php'],
			[
				[
					'Casting to int something that\'s already int.',
					6,
				],
				[
					'Casting to string something that\'s already string.',
					8,
				],
			]
		);
	}

}
