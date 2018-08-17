<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class CallStaticMethodsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkThisOnly;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($broker, true, $this->checkThisOnly, true);
		return new CallStaticMethodsRule(
			$broker,
			new FunctionCallParametersCheck($ruleLevelHelper, true, true),
			$ruleLevelHelper,
			new ClassCaseSensitivityCheck($broker),
			true,
			true
		);
	}

	public function testCallStaticMethods(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/call-static-methods.php'], [
			[
				'Call to an undefined static method CallStaticMethods\Foo::bar().',
				39,
			],
			[
				'Call to an undefined static method CallStaticMethods\Bar::bar().',
				40,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::bar().',
				41,
			],
			[
				'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
				42,
			],
			[
				'Call to private static method dolor() of class CallStaticMethods\Foo.',
				43,
			],
			[
				'CallStaticMethods\Ipsum::ipsumTest() calls parent::lorem() but CallStaticMethods\Ipsum does not extend any class.',
				63,
			],
			[
				'Static method CallStaticMethods\Foo::test() invoked with 1 parameter, 0 required.',
				65,
			],
			[
				'Call to protected static method baz() of class CallStaticMethods\Foo.',
				66,
			],
			[
				'Call to static method loremIpsum() on an unknown class CallStaticMethods\UnknownStaticMethodClass.',
				67,
			],
			[
				'Call to private method __construct() of class CallStaticMethods\ClassWithConstructor.',
				87,
			],
			[
				'Method CallStaticMethods\ClassWithConstructor::__construct() invoked with 0 parameters, 1 required.',
				87,
			],
			[
				'Calling self::someStaticMethod() outside of class scope.',
				93,
			],
			[
				'Calling static::someStaticMethod() outside of class scope.',
				94,
			],
			[
				'Calling parent::someStaticMethod() outside of class scope.',
				95,
			],
			[
				'Call to protected static method baz() of class CallStaticMethods\Foo.',
				97,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::bar().',
				98,
			],
			[
				'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
				99,
			],
			[
				'Call to private static method dolor() of class CallStaticMethods\Foo.',
				100,
			],
			[
				'Static method Locale::getDisplayLanguage() invoked with 3 parameters, 1-2 required.',
				104,
			],
			[
				'Static method CallStaticMethods\Foo::test() invoked with 3 parameters, 0 required.',
				115,
			],
			[
				'Cannot call static method foo() on int|string.',
				120,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				127,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::unknownMethod().',
				127,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				128,
			],
			[
				'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
				128,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				129,
			],
			[
				'Call to private static method dolor() of class CallStaticMethods\Foo.',
				129,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				130,
			],
			[
				'Static method CallStaticMethods\Foo::test() invoked with 3 parameters, 0 required.',
				130,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				131,
			],
			[
				'Call to static method CallStaticMethods\Foo::test() with incorrect case: TEST',
				131,
			],
			[
				'Class CallStaticMethods\Foo referenced with incorrect case: CallStaticMethods\FOO.',
				132,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::__construct().',
				144,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::nonexistent().',
				154,
			],
			[
				'Call to an undefined static method CallStaticMethods\Foo::nonexistent().',
				159,
			],
			[
				'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
				160,
			],
			[
				'Static method CallStaticMethods\ClassOrString::calledMethod() invoked with 1 parameter, 0 required.',
				173,
			],
			[
				'Cannot call static method doFoo() on interface CallStaticMethods\InterfaceWithStaticMethod.',
				208,
			],
			[
				'Call to an undefined static method CallStaticMethods\InterfaceWithStaticMethod::doBar().',
				209,
			],
			[
				'Static call to instance method CallStaticMethods\InterfaceWithStaticMethod::doInstanceFoo().',
				212,
			],
			[
				'Static call to instance method CallStaticMethods\InterfaceWithStaticMethod::doInstanceFoo().',
				213,
			],
		]);
	}

	public function testCallInterfaceMethods(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
			[
				'Call to an undefined static method InterfaceMethods\Baz::barStaticMethod().',
				27,
			],
		]);
	}

	public function testCallToIncorrectCaseMethodName(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/incorrect-static-method-case.php'], [
			[
				'Call to static method IncorrectStaticMethodCase\Foo::fooBar() with incorrect case: foobar',
				10,
			],
		]);
	}

	public function testStaticCallsToInstanceMethods(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/static-calls-to-instance-methods.php'], [
			[
				'Static call to instance method StaticCallsToInstanceMethods\Foo::doFoo().',
				10,
			],
			[
				'Static call to instance method StaticCallsToInstanceMethods\Bar::doBar().',
				16,
			],
			[
				'Static call to instance method StaticCallsToInstanceMethods\Foo::doFoo().',
				36,
			],
			[
				'Call to method StaticCallsToInstanceMethods\Foo::doFoo() with incorrect case: dofoo',
				42,
			],
			[
				'Method StaticCallsToInstanceMethods\Foo::doFoo() invoked with 1 parameter, 0 required.',
				43,
			],
			[
				'Call to private method doPrivateFoo() of class StaticCallsToInstanceMethods\Foo.',
				45,
			],
			[
				'Method StaticCallsToInstanceMethods\Foo::doFoo() invoked with 1 parameter, 0 required.',
				48,
			],
		]);
	}

	public function testStaticCallOnExpression(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/static-call-on-expression.php'], [
			[
				'Call to an undefined static method StaticCallOnExpression\Foo::doBar().',
				16,
			],
		]);
	}

	public function testStaticCallOnExpressionWithCheckDisabled(): void
	{
		$this->checkThisOnly = true;
		$this->analyse([__DIR__ . '/data/static-call-on-expression.php'], []);
	}

	public function testReturnStatic(): void
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/return-static-static-method.php'], [
			[
				'Call to an undefined static method ReturnStaticStaticMethod\Bar::doBaz().',
				24,
			],
		]);
	}

}
