<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class UnreachableTernaryElseBranchRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UnreachableTernaryElseBranchRule(
			new ConstantConditionRuleHelper(
				new ImpossibleCheckTypeHelper(
					$this->createBroker(),
					$this->getTypeSpecifier()
				)
			)
		);
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/unreachable-ternary-else-branch.php'],
			[
				[
					'Else branch is unreachable because ternary operator condition is always true.',
					6,
				],
				[
					'Else branch is unreachable because ternary operator condition is always true.',
					9,
				],
			]
		);
	}

}
