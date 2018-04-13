<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

class UselessCastRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new UselessCastRule();
	}

	public function testUselessCast(): void
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
				[
					'Casting to stdClass something that\'s already stdClass.',
					9,
				],
				[
					'Casting to float something that\'s already float.',
					26,
				],
			]
		);
	}

}
