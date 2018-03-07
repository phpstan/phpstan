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

	/**
	 * @param int[]|true $a
	 */
	public function unionOfIntegersAndTrue($a)
	{
		if ($a !== true) {
			$a = [];
		}

		if ($a !== true) {
			$a[] = 1;
		}

		if ($a !== true && count($a) === 1) {
			$a = reset($a);
		}
	}

	public function whileWithTypeChange()
	{
		$foo = null;
		while (fetch()) {
			if ($foo !== null) {

			}
			if ($foo !== 1) {

			}

			if (something()) {
				$foo = new self();
			}
		}

		while ($val = $this->returnArray()) {
			if ($val === null) {

			}
			$val = null;
		}
	}

	public function forWithTypeChange()
	{
		$foo = null;
		for (;;) {
			if ($foo !== null) {

			}
			if ($foo !== 1) {

			}

			if (something()) {
				$foo = new self();
			}
		}

		for (; $val = $this->returnArray();) {
			if ($val === null) {

			}
			$val = null;
		}
	}

	private function returnArray(): array
	{

	}

}

class Node
{

	/** @var self|null */
	private $next;

	/** @var int */
	private $id;

	public function iterate(): void
	{
		for ($node = $this; $node !== null; $node = $node->next) {
			// ...
		}
	}

	public function checkCycle()
	{
		if ($this->next !== null) {
			$iter = $this->next;
			while ($iter !== null) {
				if ($iter->id === $this->id) {
					throw new \Exception('Cycle detected.');
				}

				$iter = $iter->next;
			}
		}
	}

	public function checkAnotherCycle()
	{
		if ($this->next !== null) {
			$iter = $this->next;
			while ($iter !== false) {
				if ($iter->id === $this->id) {
					throw new \Exception('Cycle detected.');
				}

				$iter = $iter->next;
			}
		}
	}

	public function finallyNullability()
	{
		$result = null;
		try {
			if (doFoo()) {
				throw new \Exception();
			}
			$result = '1';
		} finally {
			if ($result !== null) {

			}
		}
	}

	public function checkForCycle()
	{
		if ($this->next !== null) {
			$iter = $this->next;
			for (;$iter !== null;) {
				if ($iter->id === $this->id) {
					throw new \Exception('Cycle detected.');
				}

				$iter = $iter->next;
			}
		}
	}

	public function checkAnotherForCycle()
	{
		if ($this->next !== null) {
			$iter = $this->next;
			for (;$iter !== false;) {
				if ($iter->id === $this->id) {
					throw new \Exception('Cycle detected.');
				}

				$iter = $iter->next;
			}
		}
	}

	public function looseNullCheck(?\stdClass $foo)
	{
		if ($foo == null) {
			return;
		}

		if ($foo !== null) {

		}
	}
}

class ConstantValuesComparison
{

	function testInt()
	{
		$a = 1;
		$b = 2;
		$a === $b;
	}


	function testArray()
	{
		$a = ['X' => 1];
		$b = ['X' => 2];
		$a === $b;
	}


	function testArrayTricky()
	{
		$a = ['X' => 1, 'Y' => 2];
		$b = ['X' => 2, 'Y' => 1];
		$a === $b;
	}


	function testArrayTrickyAlternative()
	{
		$a = ['X' => 1, 'Y' => 2];
		$b = ['Y' => 2, 'X' => 1];
		$a === $b;
	}

}

class PredefinedConstants
{

	public function doFoo()
	{
		DIRECTORY_SEPARATOR === '/';
		DIRECTORY_SEPARATOR === '\\';
		DIRECTORY_SEPARATOR === '//';
	}

}

class ConstantTypeInWhile
{

	public function doFoo()
	{
		$i = 0;
		while ($i++) {
			if ($i === 1000000) {

			}
			if ($i === 'string') {

			}
		}

		if ($i === 1000000) {

		}
		if ($i === 'string') {

		}
	}

}

class ConstantTypeInDoWhile
{

	public function doFoo()
	{
		$i = 0;
		do {
			if ($i === 1000000) {

			}
			if ($i === 'string') {

			}
		} while ($i++);

		if ($i === 1000000) {

		}
		if ($i === 'string') {

		}
	}

}
