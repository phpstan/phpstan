<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;

class CallStaticMethodsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallStaticMethodsRule(
			$this->getBroker(),
			new FunctionCallParametersCheck()
		);
	}

	public function testCallStaticMethods()
	{
		require_once __DIR__ . '/data/call-static-methods.php';
		$this->analyse([__DIR__ . '/data/call-static-methods.php'], [
			[
				'Call to an undefined static method CallStaticMethods\Foo::bar().',
				34,
			],
			[
				'Call to an undefined static method CallStaticMethods\Bar::bar().',
				35,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::bar().',
				36,
			],
			[
				'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
				37,
			],
			[
				'CallStaticMethods\Ipsum::ipsumTest() calls to parent::lorem() but CallStaticMethods\Ipsum does not extend any class.',
				52,
			],
			[
				'Static method CallStaticMethods\Foo::test() invoked with 1 parameter, 0 required.',
				54,
			],
			[
				'Call to protected static method baz() of class CallStaticMethods\Foo.',
				55,
			],
			[
				'Parent constructor invoked with 0 parameters, 1 required.',
				75,
			],
		]);
	}

	public function testCallInterfaceMethods()
	{
		$this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
			[
				'Call to an undefined static method Baz::barStaticMethod().',
				25,
			],
		]);
	}

}
