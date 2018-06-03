<?php

namespace Levels\ThrowValues;

class InvalidException
{

}

interface InvalidInterfaceException
{

}

interface ValidInterfaceException extends \Throwable
{

}

class Foo
{

	/**
	 * @param ValidInterfaceException $validInterface
	 * @param InvalidInterfaceException $invalidInterface
	 * @param \Exception|null $nullableException
	 * @param \Throwable|int $throwableOrInt
	 * @param int|string $intOrString
	 */
	public function doFoo(
		ValidInterfaceException $validInterface,
		InvalidInterfaceException $invalidInterface,
		?\Exception $nullableException,
		$throwableOrInt,
		$intOrString
	) {
		throw new \Exception();
		throw $validInterface;
		throw 123;
		throw new InvalidException();
		throw $invalidInterface;
		throw $nullableException;
		throw $throwableOrInt;
		throw $intOrString;
	}

}
