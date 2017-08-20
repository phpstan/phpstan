<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCertaintyInIssetRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new VariableCertaintyInIssetRule();
	}

	public function testVariableCertaintyInIsset()
	{
		$this->analyse([__DIR__ . '/data/variable-certainty-isset.php'], [
			[
				'Variable $alwaysDefinedNotNullable in isset() always exists and is not nullable.',
				11,
			],
			[
				'Variable $neverDefinedVariable in isset() is never defined.',
				19,
			],
			[
				'Variable $anotherNeverDefinedVariable in isset() is never defined.',
				39,
			],
			[
				'Variable $yetAnotherNeverDefinedVariable in isset() is never defined.',
				43,
			],
		]);
	}

}
