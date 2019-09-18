<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Rules\RuleLevelHelper;

class ReturnTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ReturnTypeRule(new FunctionReturnTypeCheck(new RuleLevelHelper($this->createBroker(), true, false, true)));
	}

	public function testReturnTypeRule(): void
	{
		require_once __DIR__ . '/data/returnTypes.php';
		$this->analyse([__DIR__ . '/data/returnTypes.php'], [
			[
				'Function ReturnTypes\returnInteger() should return int but returns string.',
				17,
			],
			[
				'Function ReturnTypes\returnObject() should return ReturnTypes\Bar but returns int.',
				27,
			],
			[
				'Function ReturnTypes\returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
				31,
			],
			[
				'Function ReturnTypes\returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
				50,
			],
			[
				'Function ReturnTypes\returnVoid() with return type void returns null but should not return anything.',
				83,
			],
			[
				'Function ReturnTypes\returnVoid() with return type void returns int but should not return anything.',
				87,
			],
		]);
	}

}
