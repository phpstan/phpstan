<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\UnusedFunctionParametersCheck;

class UnusedClosureUsesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new UnusedClosureUsesRule(new UnusedFunctionParametersCheck());
	}

	public function testUnusedClosureUses()
	{
		$this->analyse([__DIR__ . '/data/unused-closure-uses.php'], [
			[
				'Anonymous function has an unused use $unused.',
				3,
			],
			[
				'Anonymous function has an unused use $anotherUnused.',
				3,
			],
			[
				'Anonymous function has an unused use $usedInClosureUse.',
				10,
			],
		]);
	}

}
