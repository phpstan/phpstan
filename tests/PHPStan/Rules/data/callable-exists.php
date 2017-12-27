<?php

namespace CallableExists;

define('CONST_CLASS', '\CallableExists\Bar');
define('CONST_UNKNOWN_CLASS', 'Unknown');
define('CONST_METHOD', 'knownMethod');
define('CONST_UNKNOWN_METHOD', 'unknownMethod');
define('CONST_FUNC', 'knownFunction');
define('CONST_UNKNOWN_FUNC', 'unknownFunction');

function knownFunction() {}

function ordinaryFunc($param) {}
function funcWithCallableParam(callable $callableParam, $secondParam) {}

class Foo
{
		public function knownMethod() {}
		public static function knownStaticMethod() {}
}

class Bar
{
  /** @var Foo|null */
  private $foo;

  /** @var string */
  private $fooClass;

  /** @var Foo|null */
  private static $staticFoo;

  /** @var string */
  private static $staticFooClass;

	public function __construct() {

		ordinaryFunc(true);
		$this->ordinaryMethod(true);
		self::staticOrdinaryMethod(true);

		$foo = new Foo();
		$this->foo = $foo;
		self::$staticFoo = $foo;

		$fooClass = '\CallableExists\Foo';
		$this->fooClass = '\CallableExists\Foo';
		$this::$staticFooClass = '\CallableExists\Foo';

		$knownMethod = 'knownMethod';
		$unknownMethod = 'knownMethod';
		$knownFunction = 'knownFunction';
		$unknownFunction = 'unknownFunction';
		$knownCallable = [$this, 'knownMethod'];
		$unknownCallable = [$this, 'knownMethod'];

		call_user_func([$this, 'knownMethod', 'tooMuchArgs']);
		call_user_func([$this]);
		call_user_func(['unknownTarget', 'method']);
		call_user_func([5, 'method']);
		call_user_func([CONST_UNKNOWN_CLASS, 'method']);
		call_user_func([$this, 'knownMethod']);
		call_user_func([$this, 'unknownMethod']);
		call_user_func([$this, 'knownStaticMethod']);
		call_user_func([$this, $knownMethod]);
		call_user_func([$this, $unknownMethod]);
		call_user_func([$this, CONST_METHOD]);
		call_user_func([$this, CONST_UNKNOWN_METHOD]);
		call_user_func([$this, UNKNOWN_CONST]);
		call_user_func($knownFunction);
		call_user_func($unknownFunction);
		call_user_func($knownCallable);
		call_user_func($unknownCallable);
		call_user_func([$foo, 'knownMethod']);
		call_user_func([$foo, 'unknownMethod']);
		call_user_func([$foo, 'knownStaticMethod']);
		call_user_func([__CLASS__, 'knownStaticMethod']);
		call_user_func([__CLASS__, 'unknownStaticMethod']);
		call_user_func([__CLASS__, 'knownMethod']);
		call_user_func(['\CallableExists\Bar', 'knownStaticMethod']);
		call_user_func(['\CallableExists\Bar', 'unknownStaticMethod']);
		call_user_func(['\CallableExists\Bar', 'knownMethod']);
		call_user_func('\CallableExists\Bar::knownStaticMethod');
		call_user_func('\CallableExists\Bar::unknownStaticMethod');
		call_user_func('\CallableExists\Bar::knownMethod');
		call_user_func('::knownStaticMethod', true);
		call_user_func('\CallableExists\Bar::', true);
		call_user_func([CONST_CLASS, 'knownStaticMethod']);
		call_user_func([CONST_CLASS, 'unknownStaticMethod']);
		call_user_func([CONST_CLASS, 'knownMethod']);
		call_user_func([UNKNOWN_CONST, 'knownStaticMethod']);
		call_user_func('knownFunction');
		call_user_func('unknownFunction');
		call_user_func(CONST_FUNC);
		call_user_func(CONST_UNKNOWN_FUNC);
		call_user_func(UNKNOWN_CONST);
		call_user_func([$this->foo, 'knownMethod']);
		call_user_func([$this->foo, 'unknownMethod']);
		call_user_func([$this->foo, 'knownStaticMethod']);
		call_user_func([self::$staticFoo, 'knownMethod']);
		call_user_func([self::$staticFoo, 'unknownMethod']);
		call_user_func([self::$staticFoo, 'knownStaticMethod']);
		call_user_func([$fooClass, 'knownMethod']); // non-testable
		call_user_func([$fooClass, 'unknownMethod']); // non-testable
		call_user_func([$fooClass, 'knownStaticMethod']); // non-testable
		call_user_func([$this->fooClass, 'knownMethod']); // non-testable
		call_user_func([$this->fooClass, 'unknownMethod']); // non-testable
		call_user_func([$this->fooClass, 'knownStaticMethod']); // non-testable
		call_user_func([self::$staticFooClass, 'knownMethod']); // non-testable
		call_user_func([self::$staticFooClass, 'unknownMethod']); // non-testable
		call_user_func([self::$staticFooClass, 'knownStaticMethod']); // non-testable
		call_user_func([$fooClass . '', 'method']); // non-testable
		call_user_func(['\CallableExists' . '\Foo', 'method']); // non-testable
		call_user_func($fooClass . '::method'); // non-testable
		call_user_func('\CallableExists\Foo' . '::method'); // non-testable
		call_user_func([5 + 5, 'method'], true);
		call_user_func(['\CallableExists\Bar', 5 + 5], true);
		call_user_func(5 + 5, true);
		call_user_func([self::class, 'knownStaticMethod'], true);
		call_user_func([self::class, 'unknownStaticMethod'], true);
		call_user_func([self::class, 'knownMethod'], true);

		funcWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		funcWithCallableParam([$this], true);
		funcWithCallableParam(['unknownTarget', 'method'], true);
		funcWithCallableParam([5, 'method'], true);
		funcWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		funcWithCallableParam([$this, 'knownMethod'], true);
		funcWithCallableParam([$this, 'unknownMethod'], true);
		funcWithCallableParam([$this, 'knownStaticMethod'], true);
		funcWithCallableParam([$this, $knownMethod], true);
		funcWithCallableParam([$this, $unknownMethod], true);
		funcWithCallableParam([$this, CONST_METHOD], true);
		funcWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
		funcWithCallableParam([$this, UNKNOWN_CONST], true);
		funcWithCallableParam($knownFunction, true);
		funcWithCallableParam($unknownFunction, true);
		funcWithCallableParam($knownCallable, true);
		funcWithCallableParam($unknownCallable, true);
		funcWithCallableParam([$foo, 'knownMethod'], true);
		funcWithCallableParam([$foo, 'unknownMethod'], true);
		funcWithCallableParam([$foo, 'knownStaticMethod'], true);
		funcWithCallableParam([__CLASS__, 'knownStaticMethod'], true);
		funcWithCallableParam([__CLASS__, 'unknownStaticMethod'], true);
		funcWithCallableParam([__CLASS__, 'knownMethod'], true);
		funcWithCallableParam(['\CallableExists\Bar', 'knownStaticMethod'], true);
		funcWithCallableParam(['\CallableExists\Bar', 'unknownStaticMethod'], true);
		funcWithCallableParam(['\CallableExists\Bar', 'knownMethod'], true);
		funcWithCallableParam('\CallableExists\Bar::knownStaticMethod', true);
		funcWithCallableParam('\CallableExists\Bar::unknownStaticMethod', true);
		funcWithCallableParam('\CallableExists\Bar::knownMethod', true);
		funcWithCallableParam('::knownStaticMethod', true);
		funcWithCallableParam('\CallableExists\Bar::', true);
		funcWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true);
		funcWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		funcWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		funcWithCallableParam([UNKNOWN_CONST, 'knownStaticMethod'], true);
		funcWithCallableParam('knownFunction', true);
		funcWithCallableParam('unknownFunction', true);
		funcWithCallableParam(CONST_FUNC, true);
		funcWithCallableParam(CONST_UNKNOWN_FUNC, true);
		funcWithCallableParam(UNKNOWN_CONST, true);
		funcWithCallableParam([$this->foo, 'knownMethod'], true);
		funcWithCallableParam([$this->foo, 'unknownMethod'], true);
		funcWithCallableParam([$this->foo, 'knownStaticMethod'], true);
		funcWithCallableParam([self::$staticFoo, 'knownMethod'], true);
		funcWithCallableParam([self::$staticFoo, 'unknownMethod'], true);
		funcWithCallableParam([self::$staticFoo, 'knownStaticMethod'], true);
		funcWithCallableParam([$fooClass, 'knownMethod'], true); // non-testable
		funcWithCallableParam([$fooClass, 'unknownMethod'], true); // non-testable
		funcWithCallableParam([$fooClass, 'knownStaticMethod'], true); // non-testable
		funcWithCallableParam([$this->fooClass, 'knownMethod'], true); // non-testable
		funcWithCallableParam([$this->fooClass, 'unknownMethod'], true); // non-testable
		funcWithCallableParam([$this->fooClass, 'knownStaticMethod'], true); // non-testable
		funcWithCallableParam([self::$staticFooClass, 'knownMethod'], true); // non-testable
		funcWithCallableParam([self::$staticFooClass, 'unknownMethod'], true); // non-testable
		funcWithCallableParam([self::$staticFooClass, 'knownStaticMethod'], true); // non-testable
		funcWithCallableParam([$fooClass . '', 'method'], true); // non-testable
		funcWithCallableParam(['\CallableExists' . '\Foo', 'method'], true); // non-testable
		funcWithCallableParam($fooClass . '::method', true); // non-testable
		funcWithCallableParam('\CallableExists\Foo' . '::method', true); // non-testable
		funcWithCallableParam([5 + 5, 'method'], true);
		funcWithCallableParam(['\CallableExists\Bar', 5 + 5], true);
		funcWithCallableParam(5 + 5, true);
		funcWithCallableParam([self::class, 'knownStaticMethod'], true);
		funcWithCallableParam([self::class, 'unknownStaticMethod'], true);
		funcWithCallableParam([self::class, 'knownMethod'], true);

		$this->methodWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		$this->methodWithCallableParam([$this], true);
		$this->methodWithCallableParam(['unknownTarget', 'method'], true);
		$this->methodWithCallableParam([5, 'method'], true);
		$this->methodWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		$this->methodWithCallableParam([$this, 'knownMethod'], true); //OK
		$this->methodWithCallableParam([$this, 'unknownMethod'], true);
		$this->methodWithCallableParam([$this, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([$this, $knownMethod], true); // OK
		$this->methodWithCallableParam([$this, $unknownMethod], true); // non-testable
		$this->methodWithCallableParam([$this, CONST_METHOD], true); // OK
		$this->methodWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
		$this->methodWithCallableParam([$this, UNKNOWN_CONST], true);
		$this->methodWithCallableParam($knownFunction, true); //OK
		$this->methodWithCallableParam($unknownFunction, true); // non-testable
		$this->methodWithCallableParam($knownCallable, true); //OK
		$this->methodWithCallableParam($unknownCallable, true); // non-testable
		$this->methodWithCallableParam([$foo, 'knownMethod'], true); //OK
		$this->methodWithCallableParam([$foo, 'unknownMethod'], true);
		$this->methodWithCallableParam([$foo, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([__CLASS__, 'knownStaticMethod'], true); //OK
		$this->methodWithCallableParam([__CLASS__, 'unknownStaticMethod'], true);
		$this->methodWithCallableParam([__CLASS__, 'knownMethod'], true);
		$this->methodWithCallableParam(['\CallableExists\Bar', 'knownStaticMethod'], true); //OK
		$this->methodWithCallableParam(['\CallableExists\Bar', 'unknownStaticMethod'], true);
		$this->methodWithCallableParam(['\CallableExists\Bar', 'knownMethod'], true);
		$this->methodWithCallableParam('\CallableExists\Bar::knownStaticMethod', true); //OK
		$this->methodWithCallableParam('\CallableExists\Bar::unknownStaticMethod', true);
		$this->methodWithCallableParam('\CallableExists\Bar::knownMethod', true);
		$this->methodWithCallableParam('::knownStaticMethod', true);
		$this->methodWithCallableParam('\CallableExists\Bar::', true);
		$this->methodWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true); //OK
		$this->methodWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		$this->methodWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		$this->methodWithCallableParam([UNKNOWN_CONST, 'knownStaticMethod'], true);
		$this->methodWithCallableParam('knownFunction', true); //OK
		$this->methodWithCallableParam('unknownFunction', true);
		$this->methodWithCallableParam(CONST_FUNC, true); //OK
		$this->methodWithCallableParam(CONST_UNKNOWN_FUNC, true);
		$this->methodWithCallableParam(UNKNOWN_CONST, true);
		$this->methodWithCallableParam([$this->foo, 'knownMethod'], true); //OK
		$this->methodWithCallableParam([$this->foo, 'unknownMethod'], true);
		$this->methodWithCallableParam([$this->foo, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([self::$staticFoo, 'knownMethod'], true);
		$this->methodWithCallableParam([self::$staticFoo, 'unknownMethod'], true);
		$this->methodWithCallableParam([self::$staticFoo, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([$fooClass, 'knownMethod'], true); // non-testable
		$this->methodWithCallableParam([$fooClass, 'unknownMethod'], true); // non-testable
		$this->methodWithCallableParam([$fooClass, 'knownStaticMethod'], true); // non-testable
		$this->methodWithCallableParam([$this->fooClass, 'knownMethod'], true); // non-testable
		$this->methodWithCallableParam([$this->fooClass, 'unknownMethod'], true); // non-testable
		$this->methodWithCallableParam([$this->fooClass, 'knownStaticMethod'], true); // non-testable
		$this->methodWithCallableParam([self::$staticFooClass, 'knownMethod'], true); // non-testable
		$this->methodWithCallableParam([self::$staticFooClass, 'unknownMethod'], true); // non-testable
		$this->methodWithCallableParam([self::$staticFooClass, 'knownStaticMethod'], true); // non-testable
		$this->methodWithCallableParam([$fooClass . '', 'method'], true); // non-testable
		$this->methodWithCallableParam(['\CallableExists' . '\Foo', 'method'], true); // non-testable
		$this->methodWithCallableParam($fooClass . '::method', true); // non-testable
		$this->methodWithCallableParam('\CallableExists\Foo' . '::method', true); // non-testable
		$this->methodWithCallableParam([5 + 5, 'method'], true);
		$this->methodWithCallableParam(['\CallableExists\Bar', 5 + 5], true);
		$this->methodWithCallableParam(5 + 5, true);
		$this->methodWithCallableParam([self::class, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([self::class, 'unknownStaticMethod'], true);
		$this->methodWithCallableParam([self::class, 'knownMethod'], true);

		self::staticMethodWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		self::staticMethodWithCallableParam([$this], true);
		self::staticMethodWithCallableParam(['unknownTarget', 'method'], true);
		self::staticMethodWithCallableParam([5, 'method'], true);
		self::staticMethodWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		self::staticMethodWithCallableParam([$this, 'knownMethod'], true);
		self::staticMethodWithCallableParam([$this, 'unknownMethod'], true);
		self::staticMethodWithCallableParam([$this, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([$this, $knownMethod], true);
		self::staticMethodWithCallableParam([$this, $unknownMethod], true);
		self::staticMethodWithCallableParam([$this, CONST_METHOD], true);
		self::staticMethodWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
		self::staticMethodWithCallableParam([$this, UNKNOWN_CONST], true);
		self::staticMethodWithCallableParam($knownFunction, true);
		self::staticMethodWithCallableParam($unknownFunction, true);
		self::staticMethodWithCallableParam($knownCallable, true);
		self::staticMethodWithCallableParam($unknownCallable, true);
		self::staticMethodWithCallableParam([$foo, 'knownMethod'], true);
		self::staticMethodWithCallableParam([$foo, 'unknownMethod'], true);
		self::staticMethodWithCallableParam([$foo, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([__CLASS__, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([__CLASS__, 'unknownStaticMethod'], true);
		self::staticMethodWithCallableParam([__CLASS__, 'knownMethod'], true);
		self::staticMethodWithCallableParam(['\CallableExists\Bar', 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam(['\CallableExists\Bar', 'unknownStaticMethod'], true);
		self::staticMethodWithCallableParam(['\CallableExists\Bar', 'knownMethod'], true);
		self::staticMethodWithCallableParam('\CallableExists\Bar::knownStaticMethod', true);
		self::staticMethodWithCallableParam('\CallableExists\Bar::unknownStaticMethod', true);
		self::staticMethodWithCallableParam('\CallableExists\Bar::knownMethod', true);
		self::staticMethodWithCallableParam('::knownStaticMethod', true);
		self::staticMethodWithCallableParam('\CallableExists\Bar::', true);
		self::staticMethodWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		self::staticMethodWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		self::staticMethodWithCallableParam([UNKNOWN_CONST, 'knownMethod'], true);
		self::staticMethodWithCallableParam('knownFunction', true);
		self::staticMethodWithCallableParam('unknownFunction', true);
		self::staticMethodWithCallableParam(CONST_FUNC, true);
		self::staticMethodWithCallableParam(CONST_UNKNOWN_FUNC, true);
		self::staticMethodWithCallableParam(UNKNOWN_CONST, true);
		self::staticMethodWithCallableParam([$this->foo, 'knownMethod'], true);
		self::staticMethodWithCallableParam([$this->foo, 'unknownMethod'], true);
		self::staticMethodWithCallableParam([$this->foo, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([self::$staticFoo, 'knownMethod'], true);
		self::staticMethodWithCallableParam([self::$staticFoo, 'unknownMethod'], true);
		self::staticMethodWithCallableParam([self::$staticFoo, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([$fooClass, 'knownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$fooClass, 'unknownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$fooClass, 'knownStaticMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$this->fooClass, 'knownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$this->fooClass, 'unknownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$this->fooClass, 'knownStaticMethod'], true); // non-testable
		self::staticMethodWithCallableParam([self::$staticFooClass, 'knownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([self::$staticFooClass, 'unknownMethod'], true); // non-testable
		self::staticMethodWithCallableParam([self::$staticFooClass, 'knownStaticMethod'], true); // non-testable
		self::staticMethodWithCallableParam([$fooClass . '', 'method'], true); // non-testable
		self::staticMethodWithCallableParam(['\CallableExists' . '\Foo', 'method'], true); // non-testable
		self::staticMethodWithCallableParam($fooClass . '::method', true); // non-testable
		self::staticMethodWithCallableParam('\CallableExists\Foo' . '::method', true); // non-testable
		self::staticMethodWithCallableParam([5 + 5, 'method'], true);
		self::staticMethodWithCallableParam(['\CallableExists\Bar', 5 + 5], true);
		self::staticMethodWithCallableParam(5 + 5, true);
		self::staticMethodWithCallableParam([self::class, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([self::class, 'unknownStaticMethod'], true);
		self::staticMethodWithCallableParam([self::class, 'knownMethod'], true);
	}

	public function ordinaryMethod($param) {}
	public static function staticOrdinaryMethod($param) {}

	public function methodWithCallableParam(callable $callableParam, $secondParam) {}
	public static function staticMethodWithCallableParam(callable $callableParam, $secondParam) {}

	public function knownMethod() {}
	public static function knownStaticMethod() {}
}


