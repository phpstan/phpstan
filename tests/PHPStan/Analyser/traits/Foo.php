<?php declare(strict_types = 1);

namespace AnalyseTraits;

class Foo
{

	use FooTrait;

	public function doFoo(): void
	{
	}

	public function conflictingMethodWithDifferentArgumentNames(string $input): void
	{
	}

}
