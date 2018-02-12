<?php declare(strict_types = 1);

namespace PHPStan\Tests;

class AssertionClass
{
	public function assertString(?string $arg): bool
	{
		if ($arg === null) {
			throw new \Exception();
		}
		return true;
	}

	public static function assertInt(?int $arg): bool
	{
		if ($arg === null) {
			throw new \Exception();
		}
		return true;
	}
}
