<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class CallMethodsRuleTest extends \PHPStan\Testing\RuleTestCase
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
			$ruleLevelHelper,
			true,
			true
		);
	}

	public function testCallMethods(): void
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
				'Method Test\Foo::foo() invoked with 1 parameter, 0 required.',
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
				'Cannot call method ipsum() on Test\Foo|null.',
				183,
			],
			[
				'Cannot call method ipsum() on Test\Bar|null.',
				184,
			],
			[
				'Cannot call method ipsum() on Test\Foo|null.',
				201,
			],
			[
				'Cannot call method ipsum() on Test\Bar|null.',
				202,
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
				'Cannot call method ipsum() on Test\Foo|null.',
				325,
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
			[
				'Cannot call method test() on array<Test\UnknownClass>.',
				399,
			],
			[
				'Method Test\Foo::ipsum() invoked with 1 parameter, 0 required.',
				409,
			],
			[
				'Parameter #1 $test of method Test\NullableInPhpDoc::doFoo() expects string, null given.',
				427,
			],
			[
				'Parameter #1 $globalTitle of method Test\ThreeTypesCall::threeTypes() expects string, float given.',
				446,
			],
			[
				'Cannot call method find() on Test\NullCoalesce|null.',
				516,
			],
			[
				'Cannot call method find() on Test\NullCoalesce|null.',
				518,
			],
			[
				'Cannot call method find() on Test\NullCoalesce|null.',
				522,
			],
			[
				'Cannot call method find() on Test\NullCoalesce|null.',
				524,
			],
			[
				'Cannot call method find() on Test\NullCoalesce|null.',
				524,
			],
			[
				'Parameter #1 $param of method Test\IncompatiblePhpDocNullableTypeIssue::doFoo() expects string|null, int given.',
				551,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, false given.',
				565,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, Test\Foo given.',
				567,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, false given.',
				568,
			],
			[
				'Parameter #1 $s of method Test\ForeachSituation::takesInt() expects int|null, string|null given.',
				595,
			],
			[
				'Parameter #1 $str of method Test\LiteralArrayTypeCheck::test() expects string, int given.',
				632,
			],
			[
				'Parameter #1 $str of method Test\LiteralArrayTypeCheck::test() expects string, true given.',
				633,
			],
			[
				'Cannot call method add() on null.',
				647,
			],
			[
				'Parameter #1 $str of method Test\CheckIsCallable::test() expects callable(): mixed, \'nonexistentFunction\' given.',
				658,
			],
			[
				'Parameter #1 $str of method Test\CheckIsCallable::test() expects callable(): mixed, \'Test…\' given.',
				660,
			],
			[
				'Method Test\VariadicAnnotationMethod::definedInPhpDoc() invoked with 0 parameters, at least 1 required.',
				714,
			],
			[
				'Parameter #2 $str of method Test\PreIncString::doFoo() expects string, int given.',
				725,
			],
			[
				'Cannot call method bar() on string.',
				747,
			],
			[
				'Cannot call method bar() on string.',
				748,
			],
			[
				'Parameter #1 $std of method Test\CheckDefaultArrayKeys::doAmet() expects stdClass, (int|string) given.',
				791,
			],
			[
				'Parameter #1 $i of method Test\CheckDefaultArrayKeys::doBar() expects int, int|stdClass|string given.',
				797,
			],
			[
				'Parameter #1 $str of method Test\CheckDefaultArrayKeys::doBaz() expects string, int|stdClass|string given.',
				798,
			],
			[
				'Parameter #1 $intOrString of method Test\CheckDefaultArrayKeys::doLorem() expects int|string, int|stdClass|string given.',
				799,
			],
			[
				'Parameter #1 $stdOrInt of method Test\CheckDefaultArrayKeys::doIpsum() expects int|stdClass, int|stdClass|string given.', // should not expect this
				800,
			],
			[
				'Parameter #1 $stdOrString of method Test\CheckDefaultArrayKeys::doDolor() expects stdClass|string, int|stdClass|string given.', // should not expect this
				801,
			],
			[
				'Parameter #1 $dateOrString of method Test\CheckDefaultArrayKeys::doSit() expects DateTimeImmutable|string, int|stdClass|string given.',
				802,
			],
			[
				'Parameter #1 $std of method Test\CheckDefaultArrayKeys::doAmet() expects stdClass, int|stdClass|string given.',
				803,
			],
			[
				'Parameter #1 $i of method Test\CheckDefaultArrayKeys::doBar() expects int, int|string given.',
				866,
			],
			[
				'Parameter #1 $str of method Test\CheckDefaultArrayKeys::doBaz() expects string, int|string given.',
				867,
			],
			[
				'Cannot call method test() on string.',
				885,
			],
			[
				'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
				886,
			],
			[
				'Method ReflectionNamedType::getName() invoked with 1 parameter, 0 required.',
				897,
			],
			[
				'Call to an undefined method Test\Foo::lorem().',
				907,
			],
			[
				'Call to an undefined method Test\Foo::lorem().',
				911,
			],
			[
				'Parameter #1 $callable of method Test\MethodExists::doBar() expects callable(): mixed, array(object, \'bar\') given.',
				916,
			],
			[
				'Parameter #1 $callable of method Test\MethodExists::doBar() expects callable(): mixed, array(object, \'bar\') given.',
				921,
			],
			[
				'Parameter #1 $ns of method SimpleXMLElement::children() expects string, int given.',
				942,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				964,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				987,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				1005,
			],
			[
				'Call to an undefined method Test\CallAfterPropertyEmpty::doBar().',
				1072,
			],
			[
				'Call to an undefined method Test\ArraySliceWithNonEmptyArray::doesNotExist().',
				1092,
			],
			[
				'Call to an undefined method Test\AssertInFor::doBar().',
				1207,
			],
		]);
	}

	public function testCallMethodsOnThisOnly(): void
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
				'Method Test\Foo::foo() invoked with 1 parameter, 0 required.',
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
			[
				'Parameter #1 $test of method Test\NullableInPhpDoc::doFoo() expects string, null given.',
				427,
			],
			[
				'Parameter #1 $globalTitle of method Test\ThreeTypesCall::threeTypes() expects string, float given.',
				446,
			],
			[
				'Parameter #1 $param of method Test\IncompatiblePhpDocNullableTypeIssue::doFoo() expects string|null, int given.',
				551,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, false given.',
				565,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, Test\Foo given.',
				567,
			],
			[
				'Parameter #1 $i of method Test\TernaryEvaluation::doBar() expects int, false given.',
				568,
			],
			[
				'Parameter #1 $s of method Test\ForeachSituation::takesInt() expects int|null, string|null given.',
				595,
			],
			[
				'Parameter #1 $str of method Test\LiteralArrayTypeCheck::test() expects string, int given.',
				632,
			],
			[
				'Parameter #1 $str of method Test\LiteralArrayTypeCheck::test() expects string, true given.',
				633,
			],
			[
				'Parameter #1 $str of method Test\CheckIsCallable::test() expects callable(): mixed, \'nonexistentFunction\' given.',
				658,
			],
			[
				'Parameter #1 $str of method Test\CheckIsCallable::test() expects callable(): mixed, \'Test…\' given.',
				660,
			],
			[
				'Method Test\VariadicAnnotationMethod::definedInPhpDoc() invoked with 0 parameters, at least 1 required.',
				714,
			],
			[
				'Parameter #2 $str of method Test\PreIncString::doFoo() expects string, int given.',
				725,
			],
			[
				'Parameter #1 $std of method Test\CheckDefaultArrayKeys::doAmet() expects stdClass, (int|string) given.',
				791,
			],
			[
				'Parameter #1 $i of method Test\CheckDefaultArrayKeys::doBar() expects int, int|stdClass|string given.',
				797,
			],
			[
				'Parameter #1 $str of method Test\CheckDefaultArrayKeys::doBaz() expects string, int|stdClass|string given.',
				798,
			],
			[
				'Parameter #1 $intOrString of method Test\CheckDefaultArrayKeys::doLorem() expects int|string, int|stdClass|string given.',
				799,
			],
			[
				'Parameter #1 $stdOrInt of method Test\CheckDefaultArrayKeys::doIpsum() expects int|stdClass, int|stdClass|string given.', // should not expect this
				800,
			],
			[
				'Parameter #1 $stdOrString of method Test\CheckDefaultArrayKeys::doDolor() expects stdClass|string, int|stdClass|string given.', // should not expect this
				801,
			],
			[
				'Parameter #1 $dateOrString of method Test\CheckDefaultArrayKeys::doSit() expects DateTimeImmutable|string, int|stdClass|string given.',
				802,
			],
			[
				'Parameter #1 $std of method Test\CheckDefaultArrayKeys::doAmet() expects stdClass, int|stdClass|string given.',
				803,
			],
			[
				'Parameter #1 $i of method Test\CheckDefaultArrayKeys::doBar() expects int, int|string given.',
				866,
			],
			[
				'Parameter #1 $str of method Test\CheckDefaultArrayKeys::doBaz() expects string, int|string given.',
				867,
			],
			[
				'Parameter #1 $callable of method Test\MethodExists::doBar() expects callable(): mixed, array(object, \'bar\') given.',
				916,
			],
			[
				'Parameter #1 $callable of method Test\MethodExists::doBar() expects callable(): mixed, array(object, \'bar\') given.',
				921,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				964,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				987,
			],
			[
				'Parameter #1 $s of method Test\IssetCumulativeArray::doBar() expects string, int given.',
				1005,
			],
			[
				'Call to an undefined method Test\CallAfterPropertyEmpty::doBar().',
				1072,
			],
		]);
	}

	public function testCallTraitMethods(): void
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

	public function testCallTraitOverridenMethods(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-trait-overridden-methods.php'], []);
	}

	public function testCallInterfaceMethods(): void
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

	public function testClosureBind(): void
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

	public function testCallVariadicMethods(): void
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
				'Parameter #4 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects array<int, string>, array<int, int> given.',
				42,
			],
		]);
	}

	public function testCallToIncorrectCaseMethodName(): void
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

	public function testNullableParameters(): void
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

	public function testProtectedMethodCallFromParent(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/protected-method-call-from-parent.php'], []);
	}

	public function testSiblingMethodPrototype(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/sibling-method-prototype.php'], []);
	}

	public function testOverridenMethodPrototype(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/overriden-method-prototype.php'], []);
	}

	public function testCallMethodWithInheritDoc(): void
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

	public function testCallMethodWithInheritDocWithoutCurlyBraces(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/calling-method-with-inheritdoc-without-curly-braces.php'], [
			[
				'Parameter #1 $i of method MethodWithInheritDocWithoutCurlyBraces\Baz::doFoo() expects int, string given.',
				65,
			],
			[
				'Parameter #1 $str of method MethodWithInheritDocWithoutCurlyBraces\Foo::doBar() expects string, int given.',
				67,
			],
		]);
	}

	public function testCallMethodWithPhpDocsImplicitInheritance(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/calling-method-with-phpDocs-implicit-inheritance.php'], [
			[
				'Parameter #1 $i of method MethodWithPhpDocsImplicitInheritance\Baz::doFoo() expects int, string given.',
				56,
			],
			[
				'Parameter #1 $str of method MethodWithPhpDocsImplicitInheritance\Foo::doBar() expects string, int given.',
				58,
			],
		]);
	}

	public function testNegatedInstanceof(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/negated-instanceof.php'], []);
	}

	public function testInvokeMagicInvokeMethod(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/invoke-magic-method.php'], [
			[
				'Parameter #1 $foo of method InvokeMagicInvokeMethod\ClassForCallable::doFoo() expects callable(): mixed, InvokeMagicInvokeMethod\ClassForCallable given.',
				27,
			],
		]);
	}

	public function testCheckNullables(): void
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

	public function testDoNotCheckNullables(): void
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

	public function testMysqliQuery(): void
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

	public function testCallMethodsNullIssue(): void
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
	 * @param bool $checkNullables
	 */
	public function testIterables(bool $checkNullables): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = $checkNullables;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-methods-iterable.php'], [
			[
				'Parameter #1 $ids of method CallMethodsIterables\Uuid::bar() expects iterable<CallMethodsIterables\Uuid>, array<int, null> given.',
				14,
			],
			[
				'Parameter #1 $iterable of method CallMethodsIterables\Foo::acceptsSelfIterable() expects iterable<CallMethodsIterables\Foo>, iterable<CallMethodsIterables\Bar> given.',
				59,
			],
			[
				'Parameter #1 $iterable of method CallMethodsIterables\Foo::acceptsSelfIterable() expects iterable<CallMethodsIterables\Foo>, string given.',
				60,
			],
			[
				'Parameter #1 $iterableWithoutTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable, int given.',
				62,
			],
			[
				'Parameter #2 $iterableWithIterableTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable, int given.',
				62,
			],
			[
				'Parameter #3 $iterableWithConcreteTypehint of method CallMethodsIterables\Foo::doFoo() expects iterable<CallMethodsIterables\Bar>, int given.',
				62,
			],
			[
				'Parameter #4 $arrayWithIterableTypehint of method CallMethodsIterables\Foo::doFoo() expects array, int given.',
				62,
			],
			[
				'Parameter #5 $unionIterableType of method CallMethodsIterables\Foo::doFoo() expects CallMethodsIterables\Collection&iterable<CallMethodsIterables\Bar>, int given.',
				62,
			],
			[
				'Parameter #6 $mixedUnionIterableType of method CallMethodsIterables\Foo::doFoo() expects array, int given.',
				62,
			],
			[
				'Parameter #7 $unionIterableIterableType of method CallMethodsIterables\Foo::doFoo() expects CallMethodsIterables\Collection&iterable<CallMethodsIterables\Bar>, int given.',
				62,
			],
			[
				'Parameter #9 $integers of method CallMethodsIterables\Foo::doFoo() expects iterable<int>, int given.',
				62,
			],
			[
				'Parameter #10 $mixeds of method CallMethodsIterables\Foo::doFoo() expects iterable, int given.',
				62,
			],
		]);
	}

	public function testAcceptThrowable(): void
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

	public function testWithoutCheckUnionTypes(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = false;
		$this->analyse([__DIR__ . '/data/without-union-types.php'], [
			[
				'Method CallMethodsWithoutUnionTypes\Foo::doFoo() invoked with 3 parameters, 0 required.',
				14,
			],
		]);
	}

	public function testStrictTypes(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/call-methods-strict.php'], [
			[
				'Parameter #1 $foo of method Test\ClassWithToString::acceptsString() expects string, Test\ClassWithToString given.',
				7,
			],
		]);
	}

	public function testAliasedTraitsProblem(): void
	{
		$this->checkThisOnly = false;
		$this->checkNullables = true;
		$this->checkUnionTypes = true;
		$this->analyse([__DIR__ . '/data/aliased-traits-problem.php'], []);
	}

}
