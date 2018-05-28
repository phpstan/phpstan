<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCloningOfPossiblyNullRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new VariableCloningOfPossiblyNullRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/variable-cloning-possibly-null.php'], [
			[
				'Cloning possibly null variable $nullableFoo of type VariableCloning\Foo|null.',
				7,
			],
		]);
	}

}
