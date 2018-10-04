<?php

namespace Test;

class Foo
{

	private function foo()
	{
		$this->protectedMethodFromChild();
	}

	protected function bar()
	{

	}

	public function ipsum()
	{

	}

	public function test($bar)
	{

	}

}

class Bar extends Foo
{

	private function foobar()
	{

	}

	public function lorem()
	{
		$this->loremipsum(); // nonexistent
		$this->foo(1); // private from an ancestor
		$this->bar();
		$this->ipsum();
		$this->foobar();
		$this->lorem();
		$this->test(); // missing parameter

		$string = 'foo';
		$string->method();
	}

	public function dolor($foo, $bar, $baz)
	{
		// fixing PHP bug #71416
		$methodReflection = new \ReflectionMethod(Bar::class, 'dolor');
		$methodReflection->invoke(new self());
		$methodReflection->invoke(new self(), 'foo', 'bar', 'baz');
	}

	protected function protectedMethodFromChild()
	{
		$foo = new UnknownClass();
		$foo->doFoo();

		$this->returnsVoid();
		$this->dolor($this->returnsVoid(), 'bar', 'baz');

		foreach ($this->returnsVoid() as $void) {
			$this->returnsVoid();
			if ($this->returnsVoid()) {
				$this->returnsVoid();
				$this->returnsVoid() ? 'foo' : 'bar';
				doFoo() ? $this->returnsVoid() : 'bar';
				doFoo() ? 'foo' : $this->returnsVoid();
				$void = $this->returnsVoid();
				$void = $this->returnsVoid() ? 'foo' : 'bar';
				$void = doFoo() ? $this->returnsVoid() : 'bar';
				$void = doFoo() ? 'foo' : $this->returnsVoid();
				$this->returnsVoid() && 'foo';
				'foo' && $this->returnsVoid();
				$this->returnsVoid() || 'foo';
				'foo' || $this->returnsVoid();
				$void = $this->returnsVoid() && 'foo';
				$void = 'foo' && $this->returnsVoid();
				$void = $this->returnsVoid() || 'foo';
				$void = 'foo' || $this->returnsVoid();
			}
		}

		switch ($this->returnsVoid()) {
			case $this->returnsVoid():
				$this->returnsVoid();
		}
	}

	/**
	 * @return void
	 */
	private function returnsVoid()
	{
		@$this->returnsVoid();
	}

}

$f = function () {
	$arrayOfStdClass = new \ArrayObject([new \stdClass()]);
	$arrayOfStdClass->doFoo();
};

$g = function () {
	$pdo = new \PDO('dsn', 'username', 'password');
	$pdo->query();
	$pdo->query('statement');
};

class ClassWithToString
{

	public function __toString()
	{
		return 'foo';
	}

	public function acceptsString(string $foo)
	{

	}

}

function () {
	$foo = new ClassWithToString();
	$foo->acceptsString($foo);

	$closure = function () {

	};
	$closure->__invoke(1, 2, 3);

	$reflectionClass = new \ReflectionClass(Foo::class);
	$reflectionClass->newInstance();
	$reflectionClass->newInstance(1, 2, 3);
};

class ClassWithNullableProperty
{

	/** @var self|null */
	private $foo;

	public function doFoo()
	{
		if ($this->foo === null) {
			$this->foo = new self();
			$this->foo->doFoo();
		}
	}

	public function doBar(&$bar)
	{
		$i = 0;
		$this->doBar($i); // ok

		$arr = [1, 2, 3];
		$this->doBar($arr[0]); // ok
		$this->doBar(rand());
		$this->doBar(null);
	}

	public function doBaz()
	{
		/** @var Foo|null $foo */
		$foo = doFoo();

		/** @var Bar|null $bar */
		$bar = doBar();

		if ($foo === null && $bar === null) {
			throw new \Exception();
		}

		$foo->ipsum();
		$bar->ipsum();
	}

	public function doIpsum(): string
	{
		/** @var Foo|null $foo */
		$foo = doFoo();

		/** @var Bar|null $bar */
		$bar = doBar();

		if ($foo !== null && $bar === null) {
			return '';
		} elseif ($bar !== null && $foo === null) {
			return '';
		}

		$foo->ipsum();
		$bar->ipsum();

		return '';
	}

}

function () {
	$dateTimeZone = new \DateTimeZone('Europe/Prague');
	$dateTimeZone->getTransitions();
	$dateTimeZone->getTransitions(1);
	$dateTimeZone->getTransitions(1, 2);
	$dateTimeZone->getTransitions(1, 2, 3);

	$domDocument = new \DOMDocument('1.0');
	$domDocument->saveHTML();
	$domDocument->saveHTML(new \DOMNode());
	$domDocument->saveHTML(null);
};

class ReturningSomethingFromConstructor
{

	public function __construct()
	{
		return new Foo();
	}

}

function () {
	$obj = new ReturningSomethingFromConstructor();
	$foo = $obj->__construct();
};

class IssueWithEliminatingTypes
{

	public function doBar()
	{
		/** @var \DateTimeImmutable|null $date */
		$date = makeDate();
		if ($date !== null) {
			return;
		}

		if (something()) {
			$date = 'foo';
		} else {
			$date = 1;
		}

		echo $date->foo(); // is surely string|int
	}

}

interface FirstInterface
{

	public function firstMethod();

}

interface SecondInterface
{

	public function secondMethod();

}

class UnionInsteadOfIntersection
{

	public function doFoo($object)
	{
		while ($object instanceof FirstInterface && $object instanceof SecondInterface) {
			$object->firstMethod();
			$object->secondMethod();
			$object->firstMethod(1);
			$object->secondMethod(1);
		}
	}

}

class CallingOnNull
{

	public function doFoo()
	{
		$object = null;

		if ($object === null) {
			// nothing
		}

		$object->foo();
	}

}

class MethodsWithUnknownClasses
{

	/** @var FirstUnknownClass|SecondUnknownClass */
	private $foo;

	public function doFoo()
	{
		$this->foo->test();
	}

}

class IgnoreNullableUnionProperty
{

	/** @var Foo|null */
	private $foo;

	public function doFoo()
	{
		$this->foo->ipsum();
	}

}

interface WithFooMethod
{

	public function foo(): Foo;

}

interface WithFooAndBarMethod
{

	public function foo();

	public function bar();

}

class MethodsOnUnionType
{

	/** @var WithFooMethod|WithFooAndBarMethod */
	private $object;

	public function doFoo()
	{
		$this->object->foo(); // fine
		$this->object->bar(); // WithFooMethod does not have bar()
	}

}

interface SomeInterface
{

}

class MethodsOnIntersectionType
{

	public function doFoo(WithFooMethod $foo)
	{
		if ($foo instanceof SomeInterface) {
			$foo->foo();
			$foo->bar();
			$foo->foo()->test();
		}
	}

}

class ObjectTypehint
{

	public function doFoo(object $object)
	{
		$this->doFoo($object);
		$this->doBar($object);
	}

	public function doBar(Foo $foo)
	{
		$this->doFoo($foo);
		$this->doBar($foo);
	}

}

function () {
	/** @var UnknownClass[] $arrayOfAnUnknownClass */
	$arrayOfAnUnknownClass = doFoo();
	$arrayOfAnUnknownClass->test();
};

function () {
	$foo = false;
	foreach ([] as $val) {
		if ($foo === false) {
			$foo = new Foo();
		} else {
			$foo->ipsum();
			$foo->ipsum(1);
		}
	}
};

class NullableInPhpDoc
{

	/**
	 * @param string|null $test
	 */
	public function doFoo(string $test)
	{

	}

	public function doBar()
	{
		$this->doFoo(null);
	}

}

class ThreeTypesCall
{

	public function twoTypes(string $globalTitle)
	{
		if (($globalTitle = $this->threeTypes($globalTitle)) !== false) {
			return '';
		}

		return false;
	}

	public function floatType(float $globalTitle)
	{
		if (($globalTitle = $this->threeTypes($globalTitle)) !== false) {
			return '';
		}

		return false;
	}

	/**
	 * @param string $globalTitle
	 *
	 * @return int|bool|string
	 */
	private function threeTypes($globalTitle) {
		return false;
	}

}

class ScopeBelowInstanceofIsNoLongerChanged
{

	public function doBar()
	{
		$foo = doFoo();
		if ($foo instanceof Foo) {
		}

		$foo->nonexistentMethodOnFoo();
	}

}

class CallVariadicMethodWithArrayInPhpDoc
{

	/**
	 * @param array ...$args
	 */
	public function variadicMethod(...$args)
	{

	}

	/**
	 * @param string[] ...$args
	 */
	public function variadicArrayMethod(array ...$args)
	{

	}

	public function test()
	{
		$this->variadicMethod(1, 2, 3);
		$this->variadicMethod([1, 2, 3]);
		$this->variadicMethod(...[1, 2, 3]);
		$this->variadicArrayMethod(['foo', 'bar'], ['foo', 'bar']);
		$this->variadicArrayMethod(...[['foo', 'bar'], ['foo', 'bar']]);
	}

}

class NullCoalesce
{

	/** @var self|null */
	private $foo;

	public function doFoo()
	{
		$this->foo->find(1) ?? 'bar';

		if ($this->foo->find(1) ?? 'bar') {

		}

		($this->foo->find(1) ?? 'bar') ? 'foo' : 'bar';

		$this->foo->foo->find(1)->find(1);
	}

	/**
	 * @return self|null
	 */
	public function find()
	{

	}

}

class IncompatiblePhpDocNullableTypeIssue
{

	/**
	 * @param int|null $param
	 */
	public function doFoo(string $param = null)
	{

	}

	public function doBar()
	{
		$this->doFoo('foo'); // OK
		$this->doFoo(123); // error
	}

}

class TernaryEvaluation
{


	/**
	 * @param Foo|false $fooOrFalse
	 */
	public function doFoo($fooOrFalse)
	{
		$fooOrFalse ?: $this->doBar($fooOrFalse);
		$fooOrFalse ?
			$this->doBar($fooOrFalse)
			: $this->doBar($fooOrFalse);
	}

	public function doBar(int $i)
	{

	}

}

class ForeachSituation
{

	public function takesInt(int $s = null)
	{

	}

	/**
	 * @param string[] $letters
	 */
	public function takesStringArray(array $letters)
	{
		$letter = null;
		foreach ($letters as $letter) {

		}
		$this->takesInt($letter);
	}

}

class LogicalAndSupport
{

	public function doFoo()
	{
		if (($object = $this->findObject()) and $object instanceof self) {
			return $object->doFoo();
		}
	}

	/**
	 * @return object|null
	 */
	public function findObject()
	{

	}

}

class LiteralArrayTypeCheck
{

	public function test(string $str)
	{
		$data = [
			'string' => 'foo',
			'int' => 12,
			'bool' => true,
		];

		$this->test($data['string']);
		$this->test($data['int']);
		$this->test($data['bool']);
	}

}

class CallArrayKeyAfterAssigningToIt
{

	public function test()
	{
		$arr = [null, null];
		$arr[1] = new \DateTime();
		$arr[1]->add(new \DateInterval('P1D'));

		$arr[0]->add(new \DateInterval('P1D'));
	}

}

class CheckIsCallable
{

	public function test(callable $str)
	{
		$this->test('date');
		$this->test('nonexistentFunction');
		$this->test('Test\CheckIsCallable::test');
		$this->test('Test\CheckIsCallable::test2');
	}

	public function testClosure(\Closure $closure)
	{
		$this->testClosure(function () {

		});
	}

}

class ArrayKeysNull
{

	public function doFoo()
	{
		$array = [];

		/** @var \DateTimeImmutable|null $nullableDateTime */
		$nullableDateTime = doFoo();
		$array['key'] = $nullableDateTime;
		if ($array['key'] === null) {
			$array['key'] = new \DateTimeImmutable();
			echo $array['key']->format('j. n. Y');
		}
	}

	public function doBar(array $a)
	{
		if ($a['key'] === null) {
			$a['key'] = new \DateTimeImmutable();
			echo $a['key']->format('j. n. Y');
		}
	}

	public function doBaz(array $array)
	{
		if ($array['key'] === null) {
			$array['key'] = new \DateTimeImmutable();
			echo $array['key']->format('j. n. Y');
		}
	}

}

/**
 * @method void definedInPhpDoc(int $first, int ...$rest)
 */
class VariadicAnnotationMethod
{

	public function doFoo()
	{
		$this->definedInPhpDoc();
		$this->definedInPhpDoc(42);
	}

}

class PreIncString
{

	public function doFoo(int $i, string $str)
	{
		$this->doFoo(1, ++$i);
	}

}

class AnonymousClass
{

	public function doFoo()
	{
		$class = new class() {

			/** @var string */
			public $bar;

			/**
			 * @return string
			 */
			public function doBar()
			{
			}
		};
		$class->bar->bar();
		$class->doBar()->bar();
	}

}

class WeirdStaticIssueBase
{

	/**
	 * @param static|int $value
	 */
	public static function get($value)
	{
	}

}

class WeirdStaticIssueImpl extends WeirdStaticIssueBase
{

}

function () {
	/** @var WeirdStaticIssueImpl|int */
	$a = new WeirdStaticIssueImpl();

	WeirdStaticIssueImpl::get($a);
};

class CheckDefaultArrayKeys
{

	/**
	 * @param string[] $array
	 */
	public function doFoo(
		array $array
	)
	{
		foreach ($array as $key => $val) {
			$this->doBar($key);
			$this->doBaz($key);
			$this->doLorem($key);
			$this->doAmet($key);

			if (rand(0, 1) === 0) {
				$key = new \stdClass();
			}

			$this->doBar($key);
			$this->doBaz($key);
			$this->doLorem($key);
			$this->doIpsum($key);
			$this->doDolor($key);
			$this->doSit($key);
			$this->doAmet($key);
		}
	}

	public function doBar(int $i)
	{

	}

	public function doBaz(string $str)
	{

	}

	/**
	 * @param int|string $intOrString
	 */
	public function doLorem($intOrString)
	{

	}

	/**
	 * @param \stdClass|int $stdOrInt
	 */
	public function doIpsum($stdOrInt)
	{

	}

	/**
	 * @param \stdClass|string $stdOrString
	 */
	public function doDolor($stdOrString)
	{

	}

	/**
	 * @param \DateTimeImmutable|string $dateOrString
	 */
	public function doSit($dateOrString)
	{

	}

	public function doAmet(\stdClass $std)
	{

	}

	/**
	 * @param string[] $array
	 */
	public function doConsecteur(array $array)
	{
		foreach ($array as $key => $val) {
			if (rand(0, 1) === 0) {
				$key = 1;
			} else {
				$key = 'str';
			}

			$this->doBar($key);
			$this->doBaz($key);
		}
	}

}

class CallAfterEmpty
{

	public function doFoo(?string $q, ?Foo $foo)
	{
		if (empty($q)) {
			return;
		}
		if (empty($foo)) {
			return;
		}

		$q->test();
		$foo->test();
	}

}

class ReflectionTypeGetString
{

	public function doFoo(\ReflectionType $type)
	{
		echo $type->getName();
		echo $type->getName(123);
	}

}

class MethodExists
{

	public function doFoo(Foo $foo, $mixed)
	{
		$foo->lorem();
		if (method_exists($foo, 'lorem')) {
			$foo->lorem();
		}
		$foo->lorem();

		if (method_exists($mixed, 'foo')) {
			$mixed->foo();
			$this->doBar([$mixed, 'foo']);
			$this->doBar([$mixed, 'bar']);
		}

		if (is_object($mixed) && method_exists($mixed, 'foo')) {
			$this->doBar([$mixed, 'foo']);
			$this->doBar([$mixed, 'bar']);
		}
	}

	public function doBar(callable $callable)
	{

	}

}

class SimpleXMLElementPropertyTypehint
{

	public function doFoo(\SimpleXMLElement $xml)
	{
		if (!isset($xml->branches->branch)) {
			return [];
		}

		echo $xml->branches->children()->count();
		echo $xml->branches->children(123);
	}

}

class IssetCumulativeArray
{

	public function doFoo()
	{
		$arr = [1, 1, 1, 1, 2, 5, 3, 2];
		$cumulative = [];

		foreach ($arr as $val) {
			if (!isset($cumulative[$val])) {
				$cumulative[$val] = 0;
			}

			$cumulative[$val] = $cumulative[$val] + 1;
		}

		foreach ($cumulative as $c) {
			$this->doBar($c);
		}
	}

	public function doBar(string $s)
	{

	}

	public function doBaz()
	{
		$arr = [1, 1, 1, 1, 2, 5, 3, 2];
		$cumulative = [];

		foreach ($arr as $val) {
			if (isset($cumulative[$val])) {
				$cumulative[$val] = $cumulative[$val] + 1;
			} else {
				$cumulative[$val] = 1;
			}
		}

		foreach ($cumulative as $c) {
			$this->doBar($c);
		}
	}

	public function doLorem()
	{
		$arr = [1, 2, 3];
		$cumulative = [];

		foreach ($arr as $val) {
			if (isset($cumulative[$val])) {
				$cumulative[$val] = $cumulative[$val];
			}

			$cumulative[$val] = 1;
		}

		foreach ($cumulative as $c) {
			$this->doBar($c);
		}
	}

}

class DoWhileNeverIssue
{

	public function doFoo(int $i): void
	{
		$ipv4Piece = null;

		do {
			if ($ipv4Piece === null) {
				$ipv4Piece = $i;
			} else {
				$ipv4Piece = $ipv4Piece * 10 + $i;
			}

			$this->requireInt($ipv4Piece);
		} while (true);

		$this->requireInt($ipv4Piece);
	}

	private function requireInt(int $i): void
	{

	}

}

class ArrayOrNullCastToArray
{

	/**
	 * @return \ArrayObject|\self[]|null
	 */
	private function returnsArrayObjectOrNull()
	{

	}

	/**
	 * @param \self[] $array
	 * @return void
	 */
	private function operateOnArray(array $array): void {
	}

	public function doFoo(): void
	{
		$this->operateOnArray((array) $this->returnsArrayObjectOrNull());
		$this->operateOnArray((array) null);
	}

}
