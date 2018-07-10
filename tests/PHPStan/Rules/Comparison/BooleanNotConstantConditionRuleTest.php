<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class BooleanNotConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new BooleanNotConstantConditionRule(
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
		$this->analyse([__DIR__ . '/data/boolean-not.php'], [
			[
				'Negated boolean expression is always false.',
				13,
			],
			[
				'Negated boolean expression is always true.',
				18,
			],
			[
				'Negated boolean expression is always false.',
				33,
			],
			[
				'Negated boolean expression is always false.',
				40,
			],
		]);
	}

}
