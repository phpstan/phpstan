<?php declare(strict_types = 1);

namespace PHPStan\E2E;

class DynamicCallToStaticMethod
{

	public function foo(): void
	{
		$this->bar();
	}

	public static function bar(): void
	{
	}

}
