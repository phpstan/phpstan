<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCloningRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkNullables;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new VariableCloningRule($this->checkNullables);
	}

	public function testClone(): void
	{
		$this->checkNullables = true;
		$this->analyse([__DIR__ . '/data/variable-cloning.php'], [
			[
				'Cannot clone int|string.',
				11,
			],
			[
				'Cannot clone non-object variable $stringData of type string.',
				14,
			],
			[
				'Cannot clone string.',
				15,
			],
			[
				'Cannot clone non-object variable $bar of type string|VariableCloning\Foo.',
				19,
			],
			[
				'Cannot clone non-object variable $baz of type VariableCloning\Bar|VariableCloning\Foo|null.',
				23,
			],
		]);
	}

	public function testCloneWithoutCheckNullables(): void
	{
		$this->checkNullables = false;
		$this->analyse([__DIR__ . '/data/variable-cloning.php'], [
			[
				'Cannot clone int|string.',
				11,
			],
			[
				'Cannot clone non-object variable $stringData of type string.',
				14,
			],
			[
				'Cannot clone string.',
				15,
			],
			[
				'Cannot clone non-object variable $bar of type string|VariableCloning\Foo.',
				19,
			],
		]);
	}

}
