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
		require_once __DIR__ . '/data/useless-cast.php';
		$this->analyse(
			[__DIR__ . '/data/useless-cast.php'],
			[
				[
					'Casting to int something that\'s already int.',
					7,
				],
				[
					'Casting to string something that\'s already string.',
					9,
				],
				[
					'Casting to stdClass something that\'s already stdClass.',
					10,
				],
				[
					'Casting to float something that\'s already float.',
					27,
				],
				[
					'Casting to string something that\'s already string.',
					46,
				],
			]
		);
	}

}
