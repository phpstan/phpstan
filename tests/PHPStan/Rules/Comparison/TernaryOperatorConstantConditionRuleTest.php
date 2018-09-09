<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class TernaryOperatorConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new TernaryOperatorConstantConditionRule(
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
		$this->analyse([__DIR__ . '/data/ternary.php'], [
			[
				'Ternary operator condition is always true.',
				11,
			],
			[
				'Ternary operator condition is always false.',
				15,
			],
			[
				'If end else parts of ternary operator are equal',
				17,
			],
			[
				'If end else parts of ternary operator are equal',
				22,
			],
			[
				'If end else parts of ternary operator are equal',
				24,
			],
			[
				'If end else parts of ternary operator are equal',
				25,
			],
			[
				'Ternary operator is not needed. Use just condition casted to bool',
				27,
			],
		]);
	}

}
