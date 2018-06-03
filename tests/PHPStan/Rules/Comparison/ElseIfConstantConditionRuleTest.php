<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class ElseIfConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ElseIfConstantConditionRule(
			new ConstantConditionRuleHelper(
				new ImpossibleCheckTypeHelper(
					$this->getTypeSpecifier()
				)
			)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/elseif-condition.php'], [
			[
				'Elseif condition is always true.',
				18,
			],
		]);
	}

}
