<?php

class Foo
{
	public function check(string $methodName): bool
	{
		return method_exists(self::class, $methodName)
			&& method_exists('UnknownClass', $methodName)
			&& method_exists('foo', $methodName);
	}
}
