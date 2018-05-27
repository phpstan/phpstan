<?php

namespace CallCallables;

class Foo
{

	public function doFoo(
		$mixed,
		callable $callable,
		string $string,
		\Closure $closure
	)
	{
		$mixed();
		$callable();
		$string();
		$closure();

		$date = 'date';
		$date();
		$date('j. n. Y');

		$nonexistent = 'nonexistent';
		$nonexistent();
	}

	public function doBar(
		int $i
	)
	{
		[$this, 'doBar'](1);
		[$this, 'doBar']('string');
	}

	public static function doStaticBaz()
	{
		['CallCallables\Foo', 'doStaticBaz']();
		['CallCallables\Foo', 'doStaticBaz']('foo');
		'CallCallables\Foo::doStaticBaz'();
		'CallCallables\Foo::doStaticBaz'('foo');
	}

	private function privateFooMethod()
	{

	}

}

function (\Closure $closure) {
	[new Foo(), 'privateFooMethod']();
	$closure(1, 2, 3);

	$literalClosure = function (int $i, int $j = 1): void {

	};
	$literalClosure();
	$result = $literalClosure(1);

	$variadicClosure = function (int $i, int ...$j) {

	};
	$variadicClosure();
};

function () {
	$f = function(int $i) use (&$f) {
		$f(1);
		$f('foo');
	};
};

function () {
	$foo = new class () {
		public function __invoke(string $str) {

		}
	};

	$foo(1);
};

function () {
	$emptyString = '';
	$emptyString(1, 2, 3);
};

function (Bar $bar) {
	$bar();
};
