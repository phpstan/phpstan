<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Rules\RuleLevelHelper;

class ReturnTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ReturnTypeRule(new FunctionReturnTypeCheck(new \PhpParser\PrettyPrinter\Standard(), new RuleLevelHelper($this->createBroker(), true, false, true)));
	}

	public function testReturnTypeRule()
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
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns ReturnTypes\Foo.',
				90,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns ReturnTypes\Bar.',
				92,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns ReturnTypes\Bar[].',
				93,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns int.',
				94,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but empty return statement found.',
				95,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns iterable<ReturnTypes\Bar>&ReturnTypes\Collection.',
				99,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection.',
				103,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns (iterable<ReturnTypes\Foo>&ReturnTypes\AnotherCollection)|(iterable<ReturnTypes\Foo>&ReturnTypes\Collection).',
				107,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns iterable<ReturnTypes\Bar>&ReturnTypes\AnotherCollection.',
				111,
			],
			[
				'Method ReturnTypes\Foo::returnUnionIterableType() should return (iterable<ReturnTypes\Foo>&ReturnTypes\Collection)|ReturnTypes\Foo[] but returns null.',
				113,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this but returns new self().',
				132,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this but returns 1.',
				133,
			],
			[
				'Method ReturnTypes\Foo::returnThis() should return $this but returns null.',
				134,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns new self().',
				146,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns 1.',
				147,
			],
			[
				'Method ReturnTypes\Foo::returnThisOrNull() should return $this but returns $this->returnStaticThatReturnsNewStatic().',
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
		]);
	}

	public function testReturnTypeRulePhp70()
	{
		if (PHP_VERSION_ID >= 70100) {
			$this->markTestSkipped(
				'Test can be run only on PHP 7.0 - higher versions fail with the following test in the parse phase.'
			);
		}
		$this->analyse([__DIR__ . '/data/returnTypes-7.0.php'], [
			[
				'Method ReturnTypes\FooPhp70::returnInteger() should return int but empty return statement found.',
				10,
			],
		]);
	}

	public function testMisleadingTypehintsInClassWithoutNamespace()
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

	public function testOverridenTypeFromIfConditionShouldNotBeMixedAfterBranch()
	{
		$this->analyse([__DIR__ . '/data/returnTypes-overridenTypeInIfCondition.php'], [
			[
				'Method ReturnTypes\OverridenTypeInIfCondition::getAnotherAnotherStock() should return ReturnTypes\Stock but returns ReturnTypes\Stock|null.',
				15,
			],
		]);
	}

	public function testReturnStaticFromParent()
	{
		$this->analyse([__DIR__ . '/data/return-static-from-parent.php'], []);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testReturnIterable()
	{
		$this->analyse([__DIR__ . '/data/returnTypes-iterable.php'], [
			[
				'Method ReturnTypesIterable\Foo::stringIterable() should return iterable<string> but returns int[].',
				27,
			],
			[
				'Method ReturnTypesIterable\Foo::stringIterablePipe() should return iterable<string> but returns int[].',
				36,
			],
		]);
	}

}
