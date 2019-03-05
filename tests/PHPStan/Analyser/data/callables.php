<?php

namespace Callables;

use PHPStan\Testing\AnalysisBased\Utils;

class Foo
{

	public function doFoo(): float
	{
		$closure = function (): string {

		};
		$foo = $this;
		$arrayWithStaticMethod = ['Callables\\Foo', 'doBar'];
		$stringWithStaticMethod = 'Callables\\Foo::doFoo';
		$arrayWithInstanceMethod = [$this, 'doFoo'];

		Utils::assertTypeDescription($foo(), 'int');
		Utils::assertTypeDescription($closure(), 'string');
		Utils::assertTypeDescription($arrayWithStaticMethod(), 'Callables\\Bar');
		Utils::assertTypeDescription($stringWithStaticMethod(), 'float');
		Utils::assertTypeDescription($arrayWithInstanceMethod(), 'float');
		Utils::assertTypeDescription($closureObject(), 'mixed');
	}

	public function doBar(): Bar
	{

	}

	public function __invoke(): int
	{

	}

}
