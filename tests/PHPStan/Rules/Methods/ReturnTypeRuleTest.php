<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

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
		$this->analyse([__DIR__ . '/data/returnTypes.php'], [
			[
				'Method ReturnTypes\Foo::returnInteger() should return int but returns string.',
				20,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns int.',
				30,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
				34,
			],
			[
				'Method ReturnTypes\Foo::returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
				53,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns null but should not return anything.',
				86,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns int but should not return anything.',
				90,
			],
			[
				'Method ReturnTypes\Foo::returnStatic() should return static(ReturnTypes\Foo) but returns ReturnTypes\FooParent.',
				106,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns ReturnTypes\Foo.',
				139,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns ReturnTypes\Bar.',
				147,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns array<int, ReturnTypes\Bar>.',
				151,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns int.',
				155,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but empty return statement found.',
				159,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Bar>&ReturnTypes\Collection.',
				166,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection.',
				173,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns (iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection)|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection).',
				180,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Bar>&ReturnTypes\AnotherCollection.',
				187,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns null.',
				191,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns ReturnTypes\Foo.',
				219,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns int.',
				222,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns static(ReturnTypes\Foo).',
				225,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns null.',
				229,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns ReturnTypes\Foo.',
				247,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns int.',
				250,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns static(ReturnTypes\Foo).',
				259,
			],
			[
				'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns int.',
				282,
			],
			[
				'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns null.',
				285,
			],
			[
				'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns int.',
				298,
			],
			[
				'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns null.',
				301,
			],
			[
				'Method ReturnTypes\Foo::returnScalar() should return bool|float|int|string but returns stdClass.',
				323,
			],
			[
				'Method ReturnTypes\Foo::returnsNullInTernary() should return int but returns int|null.',
				342,
			],
			[
				'Method ReturnTypes\Foo::returnsNullInTernary() should return int but returns int|null.',
				348,
			],
			[
				'Method ReturnTypes\Foo::misleadingBoolReturnType() should return ReturnTypes\boolean but returns true.',
				355,
			],
			[
				'Method ReturnTypes\Foo::misleadingBoolReturnType() should return ReturnTypes\boolean but returns int.',
				358,
			],
			[
				'Method ReturnTypes\Foo::misleadingIntReturnType() should return ReturnTypes\integer but returns int.',
				368,
			],
			[
				'Method ReturnTypes\Foo::misleadingIntReturnType() should return ReturnTypes\integer but returns true.',
				371,
			],
			[
				'Method ReturnTypes\Foo::misleadingMixedReturnType() should return ReturnTypes\mixed but returns int.',
				381,
			],
			[
				'Method ReturnTypes\Foo::misleadingMixedReturnType() should return ReturnTypes\mixed but returns true.',
				384,
			],
			[
				'Method ReturnTypes\Stock::getAnotherStock() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				429,
			],
			[
				'Method ReturnTypes\Stock::returnSelfAgainError() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				484,
			],
			[
				'Method ReturnTypes\Stock::returnYetSelfAgainError() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				508,
			],
			[
				'Method ReturnTypes\ReturningSomethingFromConstructor::__construct() with return type void returns ReturnTypes\Foo but should not return anything.',
				552,
			],
			[
				'Method ReturnTypes\ReturnTernary::returnTernary() should return ReturnTypes\Foo but returns false.',
				625,
			],
			[
				'Method ReturnTypes\TrickyVoid::returnVoidOrInt() should return int|void but returns string.',
				656,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJsonOrNull() should return string|null but returns string|false|null.',
				671,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJson() should return string but returns string|false.',
				684,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJson() should return string but returns string|false.',
				687,
			],
			[
				'Method ReturnTypes\AppendedArrayReturnType::foo() should return array<int> but returns array<int, stdClass>.',
				700,
			],
			[
				'Method ReturnTypes\AppendedArrayReturnType::bar() should return array<int> but returns array<int|stdClass>.',
				710,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__toString() should return string but returns true.',
				720,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__isset() should return bool but returns int.',
				725,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__destruct() with return type void returns int but should not return anything.',
				730,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__unset() with return type void returns int but should not return anything.',
				735,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__sleep() should return array<int, string> but returns array<int, stdClass>.',
				740,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__wakeup() with return type void returns int but should not return anything.',
				747,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__set_state() should return object but returns array<string, string>.',
				752,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__clone() with return type void returns int but should not return anything.',
				757,
			],
			[
				'Method ReturnTypes\ReturnSpecifiedMethodCall::doFoo() should return string but returns string|false.',
				776,
			],
			[
				'Method ReturnTypes\ArrayFillKeysIssue::getIPs2() should return array<string, array<ReturnTypes\Foo>> but returns array<string, array<int, ReturnTypes\Bar>>.',
				815,
			],
			[
				'Method ReturnTypes\AssertThisInstanceOf::doBar() should return $this(ReturnTypes\AssertThisInstanceOf) but returns ReturnTypes\AssertThisInstanceOf&ReturnTypes\FooInterface.',
				838,
			],
			[
				'Method ReturnTypes\NestedArrayCheck::doFoo() should return array<string, bool> but returns array<string, array<int, string>>.',
				858,
			],
			[
				'Method ReturnTypes\NestedArrayCheck::doBar() should return array<string, bool> but returns array<string, array<string, string>>.',
				873,
			],
			[
				'Method ReturnTypes\Foo2::returnIntFromParent() should return int but returns string.',
				948,
			],
			[
				'Method ReturnTypes\Foo2::returnIntFromParent() should return int but returns ReturnTypes\integer.',
				951,
			],
			[
				'Method ReturnTypes\VariableOverwrittenInForeach::doFoo() should return int but returns int|string.',
				1009,
			],
			[
				'Method ReturnTypes\VariableOverwrittenInForeach::doBar() should return int but returns int|string.',
				1024,
			],
			[
				'Method ReturnTypes\ReturnStaticGeneric::instanceReturnsStatic() should return static(ReturnTypes\ReturnStaticGeneric) but returns ReturnTypes\ReturnStaticGeneric.',
				1064,
			],
		]);
	}

	public function testMisleadingTypehintsInClassWithoutNamespace(): void
	{
		$this->analyse([__DIR__ . '/data/misleadingTypehints.php'], [
			[
				'Method FooWithoutNamespace::misleadingBoolReturnType() should return boolean but returns true.',
				9,
			],
			[
				'Method FooWithoutNamespace::misleadingBoolReturnType() should return boolean but returns int.',
				13,
			],
			[
				'Method FooWithoutNamespace::misleadingIntReturnType() should return integer but returns int.',
				24,
			],
			[
				'Method FooWithoutNamespace::misleadingIntReturnType() should return integer but returns true.',
				28,
			],
			[
				'Method FooWithoutNamespace::misleadingMixedReturnType() should return mixed but returns int.',
				39,
			],
			[
				'Method FooWithoutNamespace::misleadingMixedReturnType() should return mixed but returns true.',
				43,
			],
		]);
	}

	public function testOverridenTypeFromIfConditionShouldNotBeMixedAfterBranch(): void
	{
		$this->analyse([__DIR__ . '/data/returnTypes-overridenTypeInIfCondition.php'], [
			[
				'Method ReturnTypes\OverridenTypeInIfCondition::getAnotherAnotherStock() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				15,
			],
		]);
	}

	public function testReturnStaticFromParent(): void
	{
		$this->analyse([__DIR__ . '/data/return-static-from-parent.php'], []);
	}

	public function testReturnIterable(): void
	{
		$this->analyse([__DIR__ . '/data/returnTypes-iterable.php'], [
			[
				'Method ReturnTypesIterable\Foo::stringIterable() should return iterable<string> but returns array<int, int>.',
				27,
			],
			[
				'Method ReturnTypesIterable\Foo::stringIterablePipe() should return iterable<string> but returns array<int, int>.',
				36,
			],
		]);
	}

}
