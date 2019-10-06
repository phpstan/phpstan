<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class BooleanAndConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new BooleanAndConstantConditionRule(
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
		$this->analyse([__DIR__ . '/data/boolean-and.php'], [
			[
				'Left side of && is always true.',
				15,
			],
			[
				'Right side of && is always true.',
				19,
			],
			[
				'Left side of && is always false.',
				24,
			],
			[
				'Right side of && is always false.',
				27,
			],
			[
				'Right side of && is always false.',
				30,
			],
			[
				'Right side of && is always true.',
				33,
			],
			[
				'Right side of && is always true.',
				36,
			],
			[
				'Right side of && is always true.',
				39,
			],
			[
				'Result of && is always false.',
				50,
			],
			[
				'Result of && is always true.',
				54,
			],
			[
				'Result of && is always false.',
				60,
			],
			[
				'Result of && is always true.',
				64,
			],
			[
				'Result of && is always false.',
				66,
			],
			[
				'Result of && is always false.',
				125,
			],
		]);
	}

}
