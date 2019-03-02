<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class CallToFunctionParametersRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new CallToFunctionParametersRule(
			$broker,
			new FunctionCallParametersCheck(new RuleLevelHelper($broker, true, false, true), true, true)
		);
	}

	public function testCallToFunctionWithoutParameters(): void
	{
		require_once __DIR__ . '/data/existing-function-definition.php';
		$this->analyse([__DIR__ . '/data/existing-function.php'], []);
	}

	public function testCallToFunctionWithIncorrectParameters(): void
	{
		require_once __DIR__ . '/data/incorrect-call-to-function-definition.php';
		$this->analyse([__DIR__ . '/data/incorrect-call-to-function.php'], [
			[
				'Function IncorrectCallToFunction\foo invoked with 1 parameter, 2 required.',
				5,
			],
			[
				'Function IncorrectCallToFunction\foo invoked with 3 parameters, 2 required.',
				7,
			],
			[
				'Parameter #1 $foo of function IncorrectCallToFunction\bar expects int, string given.',
				14,
			],
		]);
	}

	public function testCallToFunctionWithOptionalParameters(): void
	{
		require_once __DIR__ . '/data/call-to-function-with-optional-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/call-to-function-with-optional-parameters.php'], [
			[
				'Function CallToFunctionWithOptionalParameters\foo invoked with 3 parameters, 1-2 required.',
				9,
			],
			[
				'Parameter #1 $object of function get_class expects object, null given.',
				12,
			],
			[
				'Parameter #1 $object of function get_class expects object, object|null given.',
				16,
			],
		]);
	}

	public function testCallToFunctionWithDynamicParameters(): void
	{
		require_once __DIR__ . '/data/function-with-variadic-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/function-with-variadic-parameters.php'], [
			[
				'Function FunctionWithVariadicParameters\foo invoked with 0 parameters, at least 1 required.',
				6,
			],
			[
				'Parameter #3 ...$foo of function FunctionWithVariadicParameters\foo expects int, null given.',
				12,
			],
			[
				'Function FunctionWithVariadicParameters\bar invoked with 0 parameters, at least 1 required.',
				14,
			],
		]);
	}

	public function testCallToFunctionWithNullableDynamicParameters(): void
	{
		require_once __DIR__ . '/data/function-with-nullable-variadic-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/function-with-nullable-variadic-parameters.php'], [
			[
				'Function FunctionWithNullableVariadicParameters\foo invoked with 0 parameters, at least 1 required.',
				6,
			],
		]);
	}

	public function testCallToFunctionWithDynamicIterableParameters(): void
	{
		require_once __DIR__ . '/data/function-with-variadic-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/function-with-variadic-parameters-7.1.php'], [
			[
				'Parameter #2 ...$foo of function FunctionWithVariadicParameters\foo expects array<int, int>, iterable<string> given.',
				16,
			],
		]);
	}

	public function testCallToArrayUnique(): void
	{
		$this->analyse([__DIR__ . '/data/call-to-array-unique.php'], [
			[
				'Function array_unique invoked with 3 parameters, 1-2 required.',
				3,
			],
		]);
	}

	public function testCallToArrayMapVariadic(): void
	{
		$this->analyse([__DIR__ . '/data/call-to-array-map-unique.php'], []);
	}

	public function testCallToWeirdFunctions(): void
	{
		$this->analyse([__DIR__ . '/data/call-to-weird-functions.php'], [
			[
				'Function implode invoked with 0 parameters, 1-2 required.',
				3,
			],
			[
				'Function implode invoked with 3 parameters, 1-2 required.',
				6,
			],
			[
				'Function strtok invoked with 0 parameters, 1-2 required.',
				8,
			],
			[
				'Function strtok invoked with 3 parameters, 1-2 required.',
				11,
			],
			[
				'Function fputcsv invoked with 1 parameter, 2-5 required.',
				12,
			],
			[
				'Function imagepng invoked with 0 parameters, 1-4 required.',
				16,
			],
			[
				'Function imagepng invoked with 5 parameters, 1-4 required.',
				19,
			],
			[
				'Function locale_get_display_language invoked with 3 parameters, 1-2 required.',
				30,
			],
			[
				'Function mysqli_fetch_all invoked with 0 parameters, 1-2 required.',
				32,
			],
			[
				'Function mysqli_fetch_all invoked with 3 parameters, 1-2 required.',
				35,
			],
			[
				'Function openssl_open invoked with 7 parameters, 4-6 required.',
				37,
			],
			[
				'Function openssl_x509_parse invoked with 3 parameters, 1-2 required.',
				41,
			],
			[
				'Function openssl_pkcs12_export invoked with 6 parameters, 4-5 required.',
				47,
			],
			[
				'Parameter #1 $depth of function xdebug_call_class expects int, string given.',
				49,
			],
		]);
	}

	/**
	 * @requires PHP 7.1.1
	 */
	public function testUnpackOnAfter711(): void
	{
		if (PHP_VERSION_ID < 70101) {
			$this->markTestSkipped('This test requires PHP >= 7.1.1');
		}
		$this->analyse([__DIR__ . '/data/unpack.php'], [
			[
				'Function unpack invoked with 0 parameters, 2-3 required.',
				3,
			],
		]);
	}

	public function testUnpackOnBefore711(): void
	{
		$this->markTestIncomplete('Requires filtering the functionMap function parameters by current PHP reflection.');
		if (PHP_VERSION_ID >= 70101) {
			$this->markTestSkipped('This test requires PHP < 7.1.1');
		}
		$this->analyse([__DIR__ . '/data/unpack.php'], [
			[
				'Function unpack invoked with 0 parameters, 2 required.',
				3,
			],
			[
				'Function unpack invoked with 3 parameters, 2 required.',
				4,
			],
		]);
	}

	public function testPassingNonVariableToParameterPassedByReference(): void
	{
		require_once __DIR__ . '/data/passed-by-reference.php';
		$this->analyse([__DIR__ . '/data/passed-by-reference.php'], [
			[
				'Parameter #1 $foo of function PassedByReference\foo is passed by reference, so it expects variables only.',
				32,
			],
			[
				'Parameter #1 $foo of function PassedByReference\foo is passed by reference, so it expects variables only.',
				33,
			],
			[
				'Parameter #1 $array_arg of function reset expects array|object, null given.',
				39,
			],
		]);
	}

	public function testVariableIsNotNullAfterSeriesOfConditions(): void
	{
		require_once __DIR__ . '/data/variable-is-not-null-after-conditions.php';
		$this->analyse([__DIR__ . '/data/variable-is-not-null-after-conditions.php'], []);
	}

	public function testUnionIterableTypeShouldAcceptTypeFromOtherTypes(): void
	{
		require_once __DIR__ . '/data/union-iterable-type-issue.php';
		$this->analyse([__DIR__ . '/data/union-iterable-type-issue.php'], []);
	}

	public function testCallToFunctionInForeachCondition(): void
	{
		require_once __DIR__ . '/data/foreach-condition.php';
		$this->analyse([__DIR__ . '/data/foreach-condition.php'], [
			[
				'Parameter #1 $i of function CallToFunctionInForeachCondition\takesString expects string, int given.',
				20,
			],
		]);
	}

	public function testCallToFunctionInDoWhileLoop(): void
	{
		require_once __DIR__ . '/data/do-while-loop.php';
		$this->analyse([__DIR__ . '/data/do-while-loop.php'], []);
	}

	public function testRemoveArrayFromIterable(): void
	{
		require_once __DIR__ . '/data/remove-array-from-iterable.php';
		$this->analyse([__DIR__ . '/data/remove-array-from-iterable.php'], []);
	}

	public function testUnpackOperator(): void
	{
		$this->analyse([__DIR__ . '/data/unpack-operator.php'], [
			[
				'Parameter #2 ...$args of function sprintf expects bool|float|int|string|null, array<string> given.',
				18,
			],
			[
				'Parameter #2 ...$args of function sprintf expects bool|float|int|string|null, array<int, string> given.',
				19,
			],
			[
				'Parameter #2 ...$args of function sprintf expects bool|float|int|string|null, UnpackOperator\Foo given.',
				22,
			],
			[
				'Parameter #2 ...$args of function printf expects bool|float|int|string|null, UnpackOperator\Foo given.',
				24,
			],
		]);
	}

	public function testFunctionWithNumericParameterThatCreatedByAddition(): void
	{
		$this->analyse([__DIR__ . '/data/function-with-int-parameter-that-created-by-addition.php'], [
			[
				'Parameter #1 $decimal_number of function dechex expects int, float|int given.',
				20,
			],
		]);
	}

	public function testWhileLoopLookForAssignsInBranchesVariableExistence(): void
	{
		$this->analyse([__DIR__ . '/data/while-loop-look-for-assigns.php'], []);
	}

	public function testCallableOrClosureProblem(): void
	{
		require_once __DIR__ . '/data/callable-or-closure-problem.php';
		$this->analyse([__DIR__ . '/data/callable-or-closure-problem.php'], []);
	}

}
