<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class CallMethodsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/** @var bool */
	private $checkThisOnly;

	/** @var bool */
	private $checkNullables;

	/** @var bool */
	private $checkUnionTypes;

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($broker, $this->checkNullables, $this->checkThisOnly, $this->checkUnionTypes);
		return new CallMethodsRule(
			$broker,
			new FunctionCallParametersCheck($ruleLevelHelper, true, true),
			$ruleLevelHelper
		);
	}

	public function testCallMethods()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([ __DIR__ . '/data/call-methods.php'], [
			[
				'Call to an undefined method Test\Foo::protectedMethodFromChild().',
				10,
			],
			[
				'Call to an undefined method Test\Bar::loremipsum().',
				40,
			],
			[
				'Call to private method foo() of class Test\Foo.',
				41,
			],
			[
				'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
				46,
			],
			[
				'Cannot call method method() on string.',
				49,
			],
			[
				'Call to method doFoo() on an unknown class Test\UnknownClass.',
				63,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				66,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				68,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				70,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				72,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				75,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				76,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				77,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				78,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				79,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				81,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				83,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				84,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				85,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				86,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				90,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				91,
			],
			[
				'Call to an undefined method ArrayObject::doFoo().',
				108,
			],
			[
				'Method PDO::query() invoked with 0 parameters, 1-4 required.',
				113,
			],
			[
				'Parameter #1 $bar of method Test\ClassWithNullableProperty::doBar() is passed by reference, so it expects variables only.',
				167,
			],
			[
				'Parameter #1 $bar of method Test\ClassWithNullableProperty::doBar() is passed by reference, so it expects variables only.',
				168,
			],
			[
				'Method DateTimeZone::getTransitions() invoked with 3 parameters, 0-2 required.',
				214,
			],
			[
				'Result of method Test\ReturningSomethingFromConstructor::__construct() (void) is used.',
				234,
			],
			[
				'Cannot call method foo() on int|string.',
				254,
			],
			[
				'Method Test\FirstInterface::firstMethod() invoked with 1 parameter, 0 required.',
				281,
			],
			[
				'Method Test\SecondInterface::secondMethod() invoked with 1 parameter, 0 required.',
				282,
			],
			[
				'Cannot call method foo() on null.',
				299,
			],
			[
				'Call to method test() on an unknown class Test\FirstUnknownClass.',
				312,
			],
			[
				'Call to method test() on an unknown class Test\SecondUnknownClass.',
				312,
			],
			[
				'Call to an undefined method Test\WithFooAndBarMethod|Test\WithFooMethod::bar().',
				355,
			],
			[
				'Call to an undefined method Test\SomeInterface&Test\WithFooMethod::bar().',
				372,
			],
			[
				'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
				373,
			],
			[
				'Parameter #1 $foo of method Test\ObjectTypehint::doBar() expects Test\Foo, object given.',
				385,
			],
		]);
	}

	public function testCallMethodsOnThisOnly()
	{
		$this->checkThisOnly = true;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([ __DIR__ . '/data/call-methods.php'], [
			[
				'Call to an undefined method Test\Foo::protectedMethodFromChild().',
				10,
			],
			[
				'Call to an undefined method Test\Bar::loremipsum().',
				40,
			],
			[
				'Call to private method foo() of class Test\Foo.',
				41,
			],
			[
				'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
				46,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				66,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				68,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				70,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				72,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				75,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				76,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				77,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				78,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				79,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				81,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				83,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				84,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				85,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				86,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				90,
			],
			[
				'Result of method Test\Bar::returnsVoid() (void) is used.',
				91,
			],
			[
				'Parameter #1 $bar of method Test\ClassWithNullableProperty::doBar() is passed by reference, so it expects variables only.',
				167,
			],
			[
				'Parameter #1 $bar of method Test\ClassWithNullableProperty::doBar() is passed by reference, so it expects variables only.',
				168,
			],
			[
				'Parameter #1 $foo of method Test\ObjectTypehint::doBar() expects Test\Foo, object given.',
				385,
			],
		]);
	}

	public function testCallTraitMethods()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-trait-methods.php'], [
			[
				'Call to an undefined method CallTraitMethods\Baz::unexistentMethod().',
				26,
			],
		]);
	}

	public function testCallTraitOverridenMethods()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-trait-overridden-methods.php'], []);
	}

	public function testCallInterfaceMethods()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
			[
				'Call to an undefined method InterfaceMethods\Baz::barMethod().',
				25,
			],
		]);
	}

	public function testClosureBind()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
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
				'Call to private method privateMethod() of class CallClosureBind\Foo.',
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
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-variadic-methods.php'], [
			[
				'Method CallVariadicMethods\Foo::baz() invoked with 0 parameters, at least 1 required.',
				10,
			],
			[
				'Method CallVariadicMethods\Foo::lorem() invoked with 0 parameters, at least 2 required.',
				11,
			],
			[
				'Parameter #2 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
				32,
			],
			[
				'Parameter #3 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
				32,
			],
			[
				'Parameter #1 $int of method CallVariadicMethods\Foo::doVariadicString() expects int, string given.',
				34,
			],
			[
				'Parameter #3 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
				42,
			],
			[
				'Parameter #4 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string[], int[] given.',
				42,
			],
		]);
	}

	public function testCallToIncorrectCaseMethodName()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/incorrect-method-case.php'], [
			[
				'Call to method IncorrectMethodCase\Foo::fooBar() with incorrect case: foobar',
				10,
			],
		]);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testNullableParameters()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/nullable-parameters.php'], [
			[
				'Method NullableParameters\Foo::doFoo() invoked with 0 parameters, 2 required.',
				6,
			],
			[
				'Method NullableParameters\Foo::doFoo() invoked with 1 parameter, 2 required.',
				7,
			],
			[
				'Method NullableParameters\Foo::doFoo() invoked with 3 parameters, 2 required.',
				10,
			],
		]);
	}

	public function testProtectedMethodCallFromParent()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/protected-method-call-from-parent.php'], []);
	}

	public function testSiblingMethodPrototype()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/sibling-method-prototype.php'], []);
	}

	public function testOverridenMethodPrototype()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/overriden-method-prototype.php'], []);
	}

	public function testCallMethodWithInheritDoc()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/calling-method-with-inheritdoc.php'], [
			[
				'Parameter #1 $i of method MethodWithInheritDoc\Baz::doFoo() expects int, string given.',
				65,
			],
			[
				'Parameter #1 $str of method MethodWithInheritDoc\Foo::doBar() expects string, int given.',
				67,
			],
		]);
	}

	public function testNegatedInstanceof()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/negated-instanceof.php'], []);
	}

	public function testInvokeMagicInvokeMethod()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/invoke-magic-method.php'], [
			[
				'Parameter #1 $foo of method InvokeMagicInvokeMethod\ClassForCallable::doFoo() expects callable, InvokeMagicInvokeMethod\ClassForCallable given.',
				27,
			],
		]);
	}

	public function testCheckNullables()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/check-nullables.php'], [
			[
				'Parameter #1 $foo of method CheckNullables\Foo::doFoo() expects string, null given.',
				11,
			],
			[
				'Parameter #1 $foo of method CheckNullables\Foo::doFoo() expects string, string|null given.',
				15,
			],
		]);
	}

	public function testDoNotCheckNullables()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = false;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/check-nullables.php'], [
			[
				'Parameter #1 $foo of method CheckNullables\Foo::doFoo() expects string, null given.',
				11,
			],
		]);
	}

	public function testMysqliQuery()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = false;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/mysqli-query.php'], [
			[
				'Method mysqli::query() invoked with 0 parameters, 1-2 required.',
				4,
			],
		]);
	}

	public function testCallMethodsNullIssue()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = false;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/order.php'], []);
	}

	public function dataIterable(): array
	{
		return [
			[
				true,
			],
			[
				false,
			],
		];
	}

	/**
	 * @dataProvider dataIterable
	 * @requires PHP 7.1
	 * @param bool $checkNullables
	 */
	public function testIterables(bool $checkNullables)
	{
		$this->checkThisOnly = false;
		$this->checkNullables = $checkNullables;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-methods-iterable.php'], [
			[
				'Parameter #1 $ids of method CallMethodsIterables\Uuid::bar() expects iterable(CallMethodsIterables\Uuid[]), null[] given.',
				14,
			],
			[
				'Parameter #1 $iterable of method CallMethodsIterables\Foo::acceptsSelfIterable() expects iterable(CallMethodsIterables\Foo[]), iterable(CallMethodsIterables\Bar[]) given.',
				59,
			],
			[
				'Parameter #1 $iterable of method CallMethodsIterables\Foo::acceptsSelfIterable() expects iterable(CallMethodsIterables\Foo[]), string given.',
				60,
			],
			[
				'Parameter #1 $iterableWithoutTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable(mixed[]), int given.',
				62,
			],
			[
				'Parameter #2 $iterableWithIterableTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable(mixed[]), int given.',
				62,
			],
			[
				'Parameter #3 $iterableWithConcreteTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable(CallMethodsIterables\Bar[]), int given.',
				62,
			],
			[
				'Parameter #4 $arrayWithIterableTypehint of method CallMethodsIterables\Foo::doFoo() expects mixed[], int given.',
				62,
			],
			[
				'Parameter #5 $unionIterableType of method CallMethodsIterables\Foo::doFoo() expects CallMethodsIterables\Collection&iterable(CallMethodsIterables\Bar[]), int given.',
				62,
			],
			[
				'Parameter #6 $mixedUnionIterableType of method CallMethodsIterables\Foo::doFoo() expects (CallMethodsIterables\Bar|CallMethodsIterables\Foo)[], int given.',
				62,
			],
			[
				'Parameter #7 $unionIterableIterableType of method CallMethodsIterables\Foo::doFoo() expects CallMethodsIterables\Collection&iterable(CallMethodsIterables\Bar[]), int given.',
				62,
			],
			[
				'Parameter #9 $integers of method CallMethodsIterables\Foo::doFoo() expects iterable(int[]), int given.',
				62,
			],
			[
				'Parameter #10 $mixeds of method CallMethodsIterables\Foo::doFoo() expects iterable(mixed[]), int given.',
				62,
			],
		]);
	}

	public function testAcceptThrowable()
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/accept-throwable.php'], [
			[
				'Parameter #1 $i of method AcceptThrowable\Foo::doBar() expects int, AcceptThrowable\SomeInterface&Throwable given.',
				41,
			],
			[
				'Parameter #1 $i of method AcceptThrowable\Foo::doBar() expects int, AcceptThrowable\InterfaceExtendingThrowable given.',
				44,
			],
			[
				'Parameter #1 $i of method AcceptThrowable\Foo::doBar() expects int, AcceptThrowable\NonExceptionClass&Throwable given.',
				47,
			],
			[
				'Parameter #1 $i of method AcceptThrowable\Foo::doBar() expects int, Exception given.',
				50,
			],
		]);
	}

}
