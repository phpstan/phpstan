<?php

namespace CallClosureBind;

class Bar
{

	public function fooMethod()
	{
		\Closure::bind(function (Foo $foo) {
			$foo->privateMethod();
			$foo->nonexistentMethod();
		}, null, Foo::class);

		$this->fooMethod();
		$this->barMethod();
		$foo = new Foo();
		$foo->privateMethod();
		$foo->nonexistentMethod();

		\Closure::bind(function () {
			$this->fooMethod();
			$this->barMethod();
		}, $nonexistent, self::class);

		\Closure::bind(function (Foo $foo) {
			$foo->privateMethod();
			$foo->nonexistentMethod();
		}, null, 'CallClosureBind\Foo');

		\Closure::bind(function (Foo $foo) {
			$foo->privateMethod();
			$foo->nonexistentMethod();
		}, null, new Foo());
	}

}
