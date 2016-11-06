<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

class ReturnTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ReturnTypeRule();
	}

	public function testReturnTypeRule()
	{
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
		]);
	}

}
