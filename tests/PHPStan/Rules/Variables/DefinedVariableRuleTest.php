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
	private $checkMaybeUndefinedVariables;

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
		return new DefinedVariableRule(
			$this->cliArgumentsVariablesRegistered,
			$this->checkMaybeUndefinedVariables
		);
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
		$this->checkMaybeUndefinedVariables = true;
		$this->analyse([__DIR__ . '/data/defined-variables.php'], [
			[
				'Undefined variable: $definedLater',
				5,
			],
			[
				'Variable $definedInIfOnly might not be defined.',
				10,
			],
			[
				'Variable $definedInCases might not be defined.',
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
				'Variable $forI might not be defined.',
				250,
			],
			[
				'Undefined variable: $forJ',
				251,
			],
			[
				'Variable $variableDefinedInTry might not be defined.',
				260,
			],
			[
				'Variable $variableAvailableInAllCatches might not be defined.',
				266,
			],
			[
				'Variable $variableDefinedOnlyInOneCatch might not be defined.',
				267,
			],
			[
				'Undefined variable: $variableInBitwiseAndAssign',
				277,
			],
			[
				'Undefined variable: $variableInBitwiseAndAssign',
				278,
			],
			[
				'Variable $mightBeUndefinedInDoWhile might not be defined.',
				282,
			],
			[
				'Undefined variable: $variableInSecondCase',
				290,
			],
			[
				'Undefined variable: $variableAssignedInSecondCase',
				300,
			],
			[
				'Variable $variableInFallthroughCase might not be defined.',
				302,
			],
			[
				'Variable $variableFromDefaultFirst might not be defined.',
				312,
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
		$this->checkMaybeUndefinedVariables = true;
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
				'Variable $var3 might not be defined.',
				32,
			],
		]);
	}

	public function testCliArgumentsVariablesNotRegistered()
	{
		$this->cliArgumentsVariablesRegistered = false;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->checkMaybeUndefinedVariables = true;
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
		$this->checkMaybeUndefinedVariables = true;
		$this->analyse([__DIR__ . '/data/cli-arguments-variables.php'], [
			[
				'Undefined variable: $argc',
				5,
			],
		]);
	}

	public function dataLoopInitialAssignments(): array
	{
		return [
			[
				false,
				false,
				[],
			],
			[
				false,
				true,
				[
					[
						'Variable $i might not be defined.',
						7,
					],
					[
						'Variable $whileVar might not be defined.',
						13,
					],
				],
			],
			[
				true,
				false,
				[],
			],
			[
				true,
				true,
				[],
			],
		];
	}

	/**
	 * @dataProvider dataLoopInitialAssignments
	 * @param bool $polluteScopeWithLoopInitialAssignments
	 * @param bool $checkMaybeUndefinedVariables
	 * @param mixed[][] $expectedErrors
	 */
	public function testLoopInitialAssignments(
		bool $polluteScopeWithLoopInitialAssignments,
		bool $checkMaybeUndefinedVariables,
		array $expectedErrors
	)
	{
		$this->cliArgumentsVariablesRegistered = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->polluteScopeWithLoopInitialAssignments = $polluteScopeWithLoopInitialAssignments;
		$this->checkMaybeUndefinedVariables = $checkMaybeUndefinedVariables;
		$this->analyse([__DIR__ . '/data/loop-initial-assignments.php'], $expectedErrors);
	}

	public function dataCatchScopePollutedWithTryAssignments(): array
	{
		return [
			[
				false,
				false,
				[],
			],
			[
				false,
				true,
				[
					[
						'Variable $variableInTry might not be defined.',
						6,
					],
				],
			],
			[
				true,
				false,
				[],
			],
			[
				true,
				true,
				[],
			],
		];
	}

	/**
	 * @dataProvider dataCatchScopePollutedWithTryAssignments
	 * @param bool $polluteCatchScopeWithTryAssignments
	 * @param bool $checkMaybeUndefinedVariables
	 * @param mixed[][] $expectedErrors
	 */
	public function testCatchScopePollutedWithTryAssignments(
		bool $polluteCatchScopeWithTryAssignments,
		bool $checkMaybeUndefinedVariables,
		array $expectedErrors
	)
	{
		$this->cliArgumentsVariablesRegistered = false;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = $polluteCatchScopeWithTryAssignments;
		$this->checkMaybeUndefinedVariables = $checkMaybeUndefinedVariables;
		$this->analyse([__DIR__ . '/data/catch-scope-polluted-with-try-assignments.php'], $expectedErrors);
	}

	public function testDefineVariablesInClass()
	{
		$this->cliArgumentsVariablesRegistered = true;
		$this->polluteScopeWithLoopInitialAssignments = false;
		$this->polluteCatchScopeWithTryAssignments = false;
		$this->checkMaybeUndefinedVariables = true;
		$this->analyse([__DIR__ . '/data/define-variables-class.php'], []);
	}

}
