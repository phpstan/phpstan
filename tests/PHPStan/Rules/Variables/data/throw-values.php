<?php

namespace ThrowValues;

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

	if (rand(0, 1)) {
		throw new \Exception();
	}
	if (rand(0, 1)) {
		throw $validInterface;
	}
	if (rand(0, 1)) {
		throw 123;
	}
	if (rand(0, 1)) {
		throw new InvalidException();
	}
	if (rand(0, 1)) {
		throw $invalidInterface;
	}
	if (rand(0, 1)) {
		throw $nullableException;
	}
	if (rand(0, 1)) {
		throw foo();
	}
	if (rand(0, 1)) {
		throw new NonexistentClass();
	}
};
