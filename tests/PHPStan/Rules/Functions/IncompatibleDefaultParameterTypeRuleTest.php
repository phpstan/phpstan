<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class IncompatibleDefaultParameterTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new IncompatibleDefaultParameterTypeRule($this->createBroker());
	}

	public function testFunctions(): void
	{
		require_once __DIR__ . '/data/incompatible-default-parameter-type-functions.php';
		$this->analyse([__DIR__ . '/data/incompatible-default-parameter-type-functions.php'], [
			[
				'Default value of the parameter #1 $string (false) of function IncompatibleDefaultParameter\takesString() is incompatible with type string.',
				15,
			],
		]);
	}

	public function testMethods(): void
	{
		$this->analyse([__DIR__ . '/data/incompatible-default-parameter-type-methods.php'], [
			[
				'Default value of the parameter #6 $resource (false) of method baz() is incompatible with type resource.',
				39,
			],
			[
				'Default value of the parameter #6 $resource (false) of method bar() is incompatible with type resource.',
				49,
			],
		]);
	}

}
