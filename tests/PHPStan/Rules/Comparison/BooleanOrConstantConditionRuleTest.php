<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class BooleanOrConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new BooleanOrConstantConditionRule(
			new ConstantConditionRuleHelper(
				new ImpossibleCheckTypeHelper(
					$this->getTypeSpecifier()
				)
			)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/boolean-or.php'], [
			[
				'Left side of || is always true.',
				15,
			],
			[
				'Right side of || is always true.',
				19,
			],
			[
				'Left side of || is always false.',
				24,
			],
			[
				'Right side of || is always false.',
				27,
			],
			[
				'Right side of || is always true.',
				30,
			],
			[
				'Right side of || is always false.',
				33,
			],
			[
				'Right side of || is always false.',
				36,
			],
			[
				'Right side of || is always false.',
				39,
			],
			[
				'Result of || is always true.',
				50,
			],
			[
				'Result of || is always true.',
				54,
			],
			[
				'Result of || is always true.',
				61,
			],
			[
				'Result of || is always true.',
				65,
			],
		]);
	}

}
