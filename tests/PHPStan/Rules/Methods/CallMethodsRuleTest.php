<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;

class CallMethodsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new CallMethodsRule(
			$this->createBroker(),
			new FunctionCallParametersCheck()
		);
	}

	public function testCallMethods()
	{
		$this->analyse([ __DIR__ . '/data/call-methods.php'], [
			[
				'Call to an undefined method Test\Bar::loremipsum().',
				40,
			],
			[
				'Cannot call method Test\Foo::foo() from current scope.',
				41,
			],
			[
				'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
				46,
			],
		]);
	}

	public function testCallTraitMethods()
	{
		$this->analyse([__DIR__ . '/data/call-trait-methods.php'], [
			[
				'Call to an undefined method Baz::unexistentMethod().',
				24,
			],
		]);
	}

	public function testCallInterfaceMethods()
	{
		$this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
			[
				'Call to an undefined method Baz::barMethod().',
				23,
			],
		]);
	}

	public function testClosureBind()
	{
		$this->analyse([__DIR__ . '/data/closure-bind.php'], [
			[
				'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
				12,
			],
			[
				'Call to an undefined method CallClosureBind\Bar::barMethod().',
				16,
			],
			[
				'Cannot call method CallClosureBind\Foo::privateMethod() from current scope.',
				18,
			],
			[
				'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
				19,
			],
			[
				'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
				28,
			],
			[
				'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
				33,
			],
			[
				'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
				39,
			],
		]);
	}

	public function testCallVariadicMethods()
	{
		$this->analyse([__DIR__ . '/data/call-variadic-methods.php'], [
			[
				'Method CallVariadicMethods\Foo::baz() invoked with 0 parameters, at least 1 required.',
				10,
			],
			[
				'Method CallVariadicMethods\Foo::lorem() invoked with 0 parameters, at least 2 required.',
				11,
			],
		]);
	}

	public function testCallToIncorrectCaseMethodName()
	{
		$this->analyse([__DIR__ . '/data/incorrect-method-case.php'], [
			[
				'Call to method IncorrectMethodCase\Foo::fooBar() with incorrect case: foobar',
				10,
			],
		]);
	}

}
