<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class DefinedVariableRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/**
	 * @return \PHPStan\Rules\Rule
	 */
	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new DefinedVariableRule();
	}

	public function testDefinedVariables()
	{
		$this->analyse([__DIR__ . '/data/defined-variables.php'], [
			[
				'Undefined variable: $definedLater',
				3,
			],
			[
				'Undefined variable: $definedInIfOnly',
				8,
			],
			[
				'Undefined variable: $definedInCases',
				19,
			],
			[
				'Undefined variable: $fooParameterBeforeDeclaration',
				27,
			],
			[
				'Undefined variable: $parseStrParameter',
				32,
			],
			[
				'Undefined variable: $foo',
				37,
			],
			[
				'Undefined variable: $willBeUnset',
				42,
			],
			[
				'Undefined variable: $mustAlreadyExistWhenDividing',
				48,
			],
		]);
	}

}
