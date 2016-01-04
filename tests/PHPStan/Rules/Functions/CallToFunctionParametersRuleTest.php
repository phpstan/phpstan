<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionCallParametersCheck;

class CallToFunctionParametersRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/**
	 * @return \PHPStan\Rules\Rule
	 */
	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToFunctionParametersRule(
			$this->getBroker(),
			new FunctionCallParametersCheck()
		);
	}

	public function testCallToFunctionWithoutParameters()
	{
		require_once __DIR__ . '/data/existing-function-definition.php';
		$this->analyse([__DIR__ . '/data/existing-function.php'], []);
	}

	public function testCallToFunctionWithBadNumberOfParameters()
	{
		require_once __DIR__ . '/data/incorrect-call-to-function-definition.php';
		$this->analyse([__DIR__ . '/data/incorrect-call-to-function.php'], [
			[
				'Function foo invoked with 1 parameter, 2 required.',
				5,
			],
			[
				'Function foo invoked with 3 parameters, 2 required.',
				7,
			],
		]);
	}

	public function testCallToFunctionWithOptionalParameters()
	{
		require_once __DIR__ . '/data/call-to-function-with-optional-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/call-to-function-with-optional-parameters.php'], [
			[
				'Function foo invoked with 3 parameters, 1-2 required.',
				9,
			],
		]);
	}

	public function testCallToFunctionWithDynamicParameters()
	{
		require_once __DIR__ . '/data/function-with-variadic-parameters-definition.php';
		$this->analyse([__DIR__ . '/data/function-with-variadic-parameters.php'], [
			[
				'Function foo invoked with 0 parameters, at least 1 required.',
				5,
			],
			[
				'Function bar invoked with 0 parameters, at least 1 required.',
				9,
			],
		]);
	}

	public function testCallToArrayUnique()
	{
		$this->analyse([__DIR__ . '/data/call-to-array-unique.php'], [
			[
				'Function array_unique invoked with 3 parameters, 1-2 required.',
				3,
			],
		]);
	}

}
