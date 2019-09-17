<?php declare(strict_types = 1);

namespace PHPStan\Rules\DeadCode;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class UnreachableStatementRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UnreachableStatementRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/unreachable.php'], [
			[
				'Unreachable statement - code above always terminates.',
				12,
			],
			[
				'Unreachable statement - code above always terminates.',
				19,
			],
			[
				'Unreachable statement - code above always terminates.',
				30,
			],
			[
				'Unreachable statement - code above always terminates.',
				71,
			],
			[
				'Unreachable statement - code above always terminates.', // fixed in 0.12
				90,
			],
		]);
	}

	public function testRuleTopLevel(): void
	{
		$this->analyse([__DIR__ . '/data/unreachable-top-level.php'], [
			[
				'Unreachable statement - code above always terminates.',
				5,
			],
		]);
	}

}
