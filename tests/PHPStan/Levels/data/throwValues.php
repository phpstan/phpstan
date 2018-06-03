<?php

namespace Levels\ThrowValues;

class InvalidException {};
interface InvalidInterfaceException {};
interface ValidInterfaceException extends \Throwable {};

function () {
	/** @var ValidInterfaceException $validInterface */
	$validInterface = new \Exception();
	/** @var InvalidInterfaceException $invalidInterface */
	$invalidInterface = new \Exception();
	/** @var \Exception|null $nullableException */
	$nullableException = new \Exception();

	throw new \Exception();
	throw $validInterface;
	throw 123;
	throw new InvalidException();
	throw $invalidInterface;
	throw $nullableException;
};
