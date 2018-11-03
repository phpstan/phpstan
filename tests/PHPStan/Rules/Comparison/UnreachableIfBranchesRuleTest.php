<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class UnreachableIfBranchesRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UnreachableIfBranchesRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/unreachable-if-branches.php'], [
			[
				'Else branch is unreachable because previous condition is always true.',
				15,
			],
			[
				'Elseif branch is unreachable because previous condition is always true.',
				25,
			],
			[
				'Else branch is unreachable because previous condition is always true.',
				27,
			],
			[
				'Elseif branch is unreachable because previous condition is always true.',
				39,
			],
			[
				'Else branch is unreachable because previous condition is always true.',
				41,
			],
		]);
	}

}
