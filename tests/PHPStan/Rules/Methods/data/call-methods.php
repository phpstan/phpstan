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

}

class MissingTypeCall
{
	/**
	 * @return object|string|MissingTypeCall
	 */
	public function foo()
	{

	}

	public function test()
	{
		$this->foo()->foo();
	}

}
