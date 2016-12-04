<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionReturnTypeCheck;

class ReturnTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ReturnTypeRule(new FunctionReturnTypeCheck());
	}

	public function testReturnTypeRule()
	{
		if (PHP_VERSION_ID >= 70100) {
			$this->markTestSkipped(
				'Test can be run only on PHP 7.0 - higher versions fail with the following test in the parse phase.'
			);
		}
		$this->analyse([__DIR__ . '/data/returnTypes.php'], [
			[
				'Method ReturnTypes\Foo::returnInteger() should return int but empty return statement found.',
				16,
			],
			[
				'Method ReturnTypes\Foo::returnInteger() should return int but returns string.',
				17,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns int.',
				25,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
				26,
			],
			[
				'Method ReturnTypes\Foo::returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
				34,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns null but should not return anything.',
				57,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns int but should not return anything.',
				58,
			],
			[
				'Method ReturnTypes\Foo::returnStatic() should return static(ReturnTypes\Foo) but returns ReturnTypes\FooParent.',
				69,
			],
		]);
	}

}
