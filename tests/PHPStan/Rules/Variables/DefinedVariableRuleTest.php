<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class DefinedVariableRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/**
	 * @var bool
	 */
	private $cliArgumentsVariablesRegistered;

	/**
	 * @var bool
	 */
	private $polluteScopeWithLoopInitialAssignments;

	/**
	 * @var bool
	 */
	private $polluteCatchScopeWithTryAssignments;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new DefinedVariableRule($this->cliArgumentsVariablesRegistered);
	}

	protected function shouldPolluteScopeWithLoopInitialAssignments(): bool
	{
		return $this->polluteScopeWithLoopInitialAssignments;
	}

	protected function shouldPolluteCatchScopeWithTryAssignments(): bool
	{
		return $this->polluteCatchScopeWithTryAssignments;
	}

	public function testDefinedVariables()
	{
		require_once __DIR__ . '/data/defined-variables-definition.php';
		$this->cliArgumentsVariablesRegistered = true;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->analyse([__DIR__ . '/data/defined-variables.php'], [
			[
				'Undefined variable: $definedLater',
				5,
			],
			[
				'Undefined variable: $definedInIfOnly',
				10,
			],
			[
				'Undefined variable: $definedInCases',
				21,
			],
			[
				'Undefined variable: $fooParameterBeforeDeclaration',
				29,
			],
			[
				'Undefined variable: $parseStrParameter',
				34,
			],
			[
				'Undefined variable: $foo',
				39,
			],
			[
				'Undefined variable: $willBeUnset',
				44,
			],
			[
				'Undefined variable: $mustAlreadyExistWhenDividing',
				50,
			],
			[
				'Undefined variable: $arrayDoesNotExist',
				57,
			],
			[
				'Undefined variable: $undefinedVariable',
				59,
			],
			[
				'Undefined variable: $this',
				96,
			],
			[
				'Undefined variable: $this',
				99,
			],
			[
				'Undefined variable: $variableInEmpty',
				145,
			],
			[
				'Undefined variable: $negatedVariableInEmpty',
				156,
			],
			[
				'Undefined variable: $http_response_header',
				185,
			],
			[
				'Undefined variable: $http_response_header',
				191,
			],
			[
				'Undefined variable: $assignedInKey',
				203,
			],
			[
				'Undefined variable: $assignedInKey',
				204,
			],
			[
				'Undefined variable: $forI',
				250,
			],
			[
				'Undefined variable: $forJ',
				251,
			],
			[
				'Undefined variable: $variableAvailableInAllCatches',
				264,
			],
			[
				'Undefined variable: $variableDefinedOnlyInOneCatch',
				265,
			],
			[
				'Undefined variable: $variableInBitwiseAndAssign',
				275,
			],
			[
				'Undefined variable: $variableInBitwiseAndAssign',
				276,
			],
		]);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testDefinedVariablesInShortArrayDestructuringSyntax()
	{
		$this->cliArgumentsVariablesRegistered = true;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->analyse([__DIR__ . '/data/defined-variables-array-destructuring-short-syntax.php'], [
			[
				'Undefined variable: $f',
				11,
			],
			[
				'Undefined variable: $f',
				14,
			],
			[
				'Undefined variable: $var3',
				32,
			],
		]);
	}

	public function testCliArgumentsVariablesNotRegistered()
	{
		$this->cliArgumentsVariablesRegistered = false;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->analyse([__DIR__ . '/data/cli-arguments-variables.php'], [
			[
				'Undefined variable: $argc',
				3,
			],
			[
				'Undefined variable: $argc',
				5,
			],
		]);
	}

	public function testCliArgumentsVariablesRegistered()
	{
		$this->cliArgumentsVariablesRegistered = true;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->analyse([__DIR__ . '/data/cli-arguments-variables.php'], [
			[
				'Undefined variable: $argc',
				5,
			],
		]);
	}

}
