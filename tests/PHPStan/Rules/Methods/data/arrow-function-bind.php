<?php // lint >= 7.4

namespace CallArrowFunctionBind;

class Foo
{

	private function privateMethod()
	{

	}

	public function publicMethod()
	{

	}

}


class Bar
{

	public function fooMethod(): Foo
	{
		\Closure::bind(fn (Foo $foo) => $foo->privateMethod(), null, Foo::class);
		\Closure::bind(fn (Foo $foo) => $foo->nonexistentMethod(), null, Foo::class);
		\Closure::bind(fn () => $this->fooMethod(), $nonexistent, self::class);
		\Closure::bind(fn () => $this->barMethod(), $nonexistent, self::class);
		\Closure::bind(fn (Foo $foo) => $foo->privateMethod(), null, 'CallArrowFunctionBind\Foo');
		\Closure::bind(fn (Foo $foo) => $foo->nonexistentMethod(), null, 'CallArrowFunctionBind\Foo');
		\Closure::bind(fn (Foo $foo) => $foo->privateMethod(), null, new Foo());
		\Closure::bind(fn (Foo $foo) => $foo->nonexistentMethod(), null, new Foo());
		\Closure::bind(fn () => $this->privateMethod(), $this->fooMethod(), Foo::class);
		\Closure::bind(fn () => $this->nonexistentMethod(), $this->fooMethod(), Foo::class);

		(fn () => $this->publicMethod())->call(new Foo());
	}

}
