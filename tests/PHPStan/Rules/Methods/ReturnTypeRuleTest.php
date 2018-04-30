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
				16,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns int.',
				24,
			],
			[
				'Method ReturnTypes\Foo::returnObject() should return ReturnTypes\Bar but returns ReturnTypes\Foo.',
				25,
			],
			[
				'Method ReturnTypes\Foo::returnChild() should return ReturnTypes\Foo but returns ReturnTypes\OtherInterfaceImpl.',
				33,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns null but should not return anything.',
				56,
			],
			[
				'Method ReturnTypes\Foo::returnVoid() with return type void returns int but should not return anything.',
				57,
			],
			[
				'Method ReturnTypes\Foo::returnStatic() should return static(ReturnTypes\Foo) but returns ReturnTypes\FooParent.',
				68,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns ReturnTypes\Foo.',
				90,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns ReturnTypes\Bar.',
				92,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns array<int, ReturnTypes\Bar>.',
				93,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns int.',
				94,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but empty return statement found.',
				95,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Bar>&ReturnTypes\Collection.',
				99,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection.',
				103,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns (iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection)|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection).',
				107,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns iterable<ReturnTypes\Bar>&ReturnTypes\AnotherCollection.',
				111,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return array<ReturnTypes\Foo>|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection) but returns null.',
				113,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns ReturnTypes\Foo.',
				132,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns int.',
				133,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this(ReturnTypes\Foo) but returns null.',
				134,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns ReturnTypes\Foo.',
				146,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns int.',
				147,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this(ReturnTypes\Foo)|null but returns static(ReturnTypes\Foo).',
				150,
			],
			[
				'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns int.',
				165,
			],
			[
				'Method ReturnTypes\Foo::returnsParent() should return ReturnTypes\FooParent but returns null.',
				166,
			],
			[
				'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns int.',
				172,
			],
			[
				'Method ReturnTypes\Foo::returnsPhpDocParent() should return ReturnTypes\FooParent but returns null.',
				173,
			],
			[
				'Method ReturnTypes\Foo::returnScalar() should return bool|float|int|string but returns stdClass.',
				185,
			],
			[
				'Method ReturnTypes\Foo::returnsNullInTernary() should return int but returns int|null.',
				201,
			],
			[
				'Method ReturnTypes\Foo::returnsNullInTernary() should return int but returns int|null.',
				203,
			],
			[
				'Method ReturnTypes\Foo::misleadingBoolReturnType() should return ReturnTypes\boolean but returns true.',
				208,
			],
			[
				'Method ReturnTypes\Foo::misleadingBoolReturnType() should return ReturnTypes\boolean but returns int.',
				209,
			],
			[
				'Method ReturnTypes\Foo::misleadingIntReturnType() should return ReturnTypes\integer but returns int.',
				215,
			],
			[
				'Method ReturnTypes\Foo::misleadingIntReturnType() should return ReturnTypes\integer but returns true.',
				216,
			],
			[
				'Method ReturnTypes\Foo::misleadingMixedReturnType() should return ReturnTypes\mixed but returns int.',
				222,
			],
			[
				'Method ReturnTypes\Foo::misleadingMixedReturnType() should return ReturnTypes\mixed but returns true.',
				223,
			],
			[
				'Method ReturnTypes\Stock::getAnotherStock() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				265,
			],
			[
				'Method ReturnTypes\Stock::returnSelfAgainError() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				320,
			],
			[
				'Method ReturnTypes\Stock::returnYetSelfAgainError() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				344,
			],
			[
				'Method ReturnTypes\ReturningSomethingFromConstructor::__construct() with return type void returns ReturnTypes\Foo but should not return anything.',
				388,
			],
			[
				'Method ReturnTypes\ReturnTernary::returnTernary() should return ReturnTypes\Foo but returns false.',
				454,
			],
			[
				'Method ReturnTypes\TrickyVoid::returnVoidOrInt() should return int|void but returns string.',
				479,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJsonOrNull() should return string|null but returns string|false|null.',
				490,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJson() should return string but returns string|false.',
				497,
			],
			[
				'Method ReturnTypes\TernaryWithJsonEncode::toJson() should return string but returns string|false.',
				498,
			],
			[
				'Method ReturnTypes\AppendedArrayReturnType::foo() should return array<int> but returns array<int, stdClass>.',
				510,
			],
			[
				'Method ReturnTypes\AppendedArrayReturnType::bar() should return array<int> but returns array<int|stdClass>.',
				520,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__toString() should return string but returns true.',
				530,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__isset() should return bool but returns int.',
				535,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__destruct() with return type void returns int but should not return anything.',
				540,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__unset() with return type void returns int but should not return anything.',
				545,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__sleep() should return array<int, string> but returns array<int, stdClass>.',
				550,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__wakeup() with return type void returns int but should not return anything.',
				557,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__set_state() should return object but returns array<string, string>.',
				562,
			],
			[
				'Method ReturnTypes\WrongMagicMethods::__clone() with return type void returns int but should not return anything.',
				567,
			],
			[
				'Method ReturnTypes\ReturnSpecifiedMethodCall::doFoo() should return string but returns string|false.',
				586,
			],
		]);
	}

	public function testMisleadingTypehintsInClassWithoutNamespace(): void
	{
		$this->analyse([__DIR__ . '/data/misleadingTypehints.php'], [
			[
				'Method FooWithoutNamespace::misleadingBoolReturnType() should return boolean but returns true.',
				8,
			],
			[
				'Method FooWithoutNamespace::misleadingBoolReturnType() should return boolean but returns int.',
				9,
			],
			[
				'Method FooWithoutNamespace::misleadingIntReturnType() should return integer but returns int.',
				15,
			],
			[
				'Method FooWithoutNamespace::misleadingIntReturnType() should return integer but returns true.',
				16,
			],
			[
				'Method FooWithoutNamespace::misleadingMixedReturnType() should return mixed but returns int.',
				22,
			],
			[
				'Method FooWithoutNamespace::misleadingMixedReturnType() should return mixed but returns true.',
				23,
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
