<?php

namespace IncompatibleDefaultParameter;

class FooParent
{

	/**
	 * @param int $int
	 * @param string $string
	 * @param ?float $float
	 * @param \stdClass|false $object
	 * @param bool $bool
	 * @param resource $resource
	 */
	public function bar(
		$int,
		$string,
		$float,
		$object,
		$bool,
		$resource
	): void {
	}

}

class Foo extends FooParent
{

	/**
	 * @param int $int
	 * @param string $string
	 * @param ?float $float
	 * @param \stdClass|false $object
	 * @param bool $bool
	 * @param resource $resource
	 */
	public function baz(
		$int = 10,
		$string = 'string',
		$float = null,
		$object = false,
		$bool = null,
		$resource = false
	): void {
	}

	public function bar(
		$int = 10,
		$string = 'string',
		$float = null,
		$object = false,
		$bool = null,
		$resource = false
	): void {
	}

}

class PasswordConstant
{

	public function doFoo(int $algorithm = PASSWORD_DEFAULT)
	{

	}

}
