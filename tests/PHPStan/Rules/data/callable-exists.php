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
	public function __construct() {

		ordinaryFunc(true);
		$this->ordinaryMethod(true);
		self::staticOrdinaryMethod(true);

		$foo = new Foo();
		$knownMethod = 'knownMethod';
		$unknownMethod = 'knownMethod';
		$knownFunction = 'knownFunction';
		$unknownFunction = 'unknownFunction';
		$knownCallable = [$this, 'knownMethod'];
		$unknownCallable = [$this, 'knownMethod'];

		call_user_func([$this, 'knownMethod', 'tooMuchArgs']);
		call_user_func([$this]);
		call_user_func(['unknownTarget', 'method']);
		call_user_func([CONST_UNKNOWN_CLASS, 'method']);
		call_user_func([$this, 'knownMethod']);
		call_user_func([$this, 'unknownMethod']);
		call_user_func([$this, 'knownStaticMethod']);
		call_user_func([$this, $knownMethod]);
		call_user_func([$this, $unknownMethod]);
		call_user_func([$this, CONST_METHOD]);
		call_user_func([$this, CONST_UNKNOWN_METHOD]);
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
		call_user_func([CONST_CLASS, 'knownStaticMethod']);
		call_user_func([CONST_CLASS, 'unknownStaticMethod']);
		call_user_func([CONST_CLASS, 'knownMethod']);
		call_user_func('knownFunction');
		call_user_func('unknownFunction');
		call_user_func(CONST_FUNC);
		call_user_func(CONST_UNKNOWN_FUNC);

		funcWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		funcWithCallableParam([$this], true);
		funcWithCallableParam(['unknownTarget', 'method'], true);
		funcWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		funcWithCallableParam([$this, 'knownMethod'], true);
		funcWithCallableParam([$this, 'unknownMethod'], true);
		funcWithCallableParam([$this, 'knownStaticMethod'], true);
		funcWithCallableParam([$this, $knownMethod], true);
		funcWithCallableParam([$this, $unknownMethod], true);
		funcWithCallableParam([$this, CONST_METHOD], true);
		funcWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
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
		funcWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true);
		funcWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		funcWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		funcWithCallableParam('knownFunction', true);
		funcWithCallableParam('unknownFunction', true);
		funcWithCallableParam(CONST_FUNC, true);
		funcWithCallableParam(CONST_UNKNOWN_FUNC, true);

		$this->methodWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		$this->methodWithCallableParam([$this], true);
		$this->methodWithCallableParam(['unknownTarget', 'method'], true);
		$this->methodWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		$this->methodWithCallableParam([$this, 'knownMethod'], true); //OK
		$this->methodWithCallableParam([$this, 'unknownMethod'], true);
		$this->methodWithCallableParam([$this, 'knownStaticMethod'], true);
		$this->methodWithCallableParam([$this, $knownMethod], true); // OK
		$this->methodWithCallableParam([$this, $unknownMethod], true); // non-testable
		$this->methodWithCallableParam([$this, CONST_METHOD], true); // OK
		$this->methodWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
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
		$this->methodWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true); //OK
		$this->methodWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		$this->methodWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		$this->methodWithCallableParam('knownFunction', true); //OK
		$this->methodWithCallableParam('unknownFunction', true);
		$this->methodWithCallableParam(CONST_FUNC, true); //OK
		$this->methodWithCallableParam(CONST_UNKNOWN_FUNC, true);

		self::staticMethodWithCallableParam([$this, 'knownMethod', 'tooMuchArgs'], true);
		self::staticMethodWithCallableParam([$this], true);
		self::staticMethodWithCallableParam(['unknownTarget', 'method'], true);
		self::staticMethodWithCallableParam([CONST_UNKNOWN_CLASS, 'method'], true);
		self::staticMethodWithCallableParam([$this, 'knownMethod'], true);
		self::staticMethodWithCallableParam([$this, 'unknownMethod'], true);
		self::staticMethodWithCallableParam([$this, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([$this, $knownMethod], true);
		self::staticMethodWithCallableParam([$this, $unknownMethod], true);
		self::staticMethodWithCallableParam([$this, CONST_METHOD], true);
		self::staticMethodWithCallableParam([$this, CONST_UNKNOWN_METHOD], true);
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
		self::staticMethodWithCallableParam([CONST_CLASS, 'knownStaticMethod'], true);
		self::staticMethodWithCallableParam([CONST_CLASS, 'unknownStaticMethod'], true);
		self::staticMethodWithCallableParam([CONST_CLASS, 'knownMethod'], true);
		self::staticMethodWithCallableParam('knownFunction', true);
		self::staticMethodWithCallableParam('unknownFunction', true);
		self::staticMethodWithCallableParam(CONST_FUNC, true);
		self::staticMethodWithCallableParam(CONST_UNKNOWN_FUNC, true);
	}

	public function ordinaryMethod($param) {}
	public static function staticOrdinaryMethod($param) {}

	public function methodWithCallableParam(callable $callableParam, $secondParam) {}
	public static function staticMethodWithCallableParam(callable $callableParam, $secondParam) {}

	public function knownMethod() {}
	public static function knownStaticMethod() {}
}


