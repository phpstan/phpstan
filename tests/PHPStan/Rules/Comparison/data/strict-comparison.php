<?php

namespace StrictComparison;

class Foo
{

	public function doFoo()
	{
		1 === 1;
		1 === '1'; // wrong
		1 !== '1'; // wrong
		doFoo() === doBar();
		1 === null;
		(new Bar()) === 1; // wrong

		/** @var Foo[]|Collection|bool $unionIterableType */
		$unionIterableType = doFoo();
		1 === $unionIterableType;
		false === $unionIterableType;
		$unionIterableType === [new Foo()];
		$unionIterableType === new Collection();

		/** @var bool $boolean */
		$boolean = doFoo();
		true === $boolean;
		false === $boolean;
		$boolean === true;
		$boolean === false;
		true === false;
		false === true;

		$foo = new self();
		$this === $foo;

		$trueOrFalseInSwitch = false;
		switch ('foo') {
			case 'foo':
				$trueOrFalseInSwitch = true;
				break;
		}
		if ($trueOrFalseInSwitch === true) {

		}

		1.0 === 1;
		1 === 1.0;

		/** @var string|mixed $stringOrMixed */
		$stringOrMixed = doFoo();
		$stringOrMixed === 'foo';
	}

	public function doBar(string $a = null, string $b = null): string
	{
		if ($a === null && $b === null) {
			return 'no value';
		}

		if ($a !== null && $b !== null) {
			return $a . $b;
		}

		return '';
	}

	public function acceptsString(string $a)
	{
		if ($a === null) {

		}
	}

	public function anotherAcceptsString(string $a)
	{
		if ($a !== null) {

		}
	}

	public function foreachWithTypeChange()
	{
		$foo = null;
		foreach ([] as $val) {
			if ($foo !== null) {

			}
			if ($foo !== 1) {

			}

			if (something()) {
				$foo = new self();
			}
		}

		foreach ([1, 2, 3] as $val) {
			if ($val === null) {

			}
			$val = null;
		}
	}

}
