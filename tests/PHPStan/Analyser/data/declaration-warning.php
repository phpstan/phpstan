<?php

namespace DeclarationWarning;

@mkdir('/foo/bar');

require __DIR__ . '/trigger-warning.php';

class Foo
{

	public function doFoo(): void
	{

	}

}

class Bar extends Foo
{

	public function doFoo(int $i): void
	{

	}

}
