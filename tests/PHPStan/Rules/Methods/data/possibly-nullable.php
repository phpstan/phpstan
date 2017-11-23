<?php

namespace CallingMethodOnPossiblyNullable;

class Test {

	/** @var \DateTimeImmutable|null */
	private $date;

	public function __construct() {
		$this->date->format('Y');

		if (!is_null($this->date)) {
			$this->date->format('Y');
		}
	}

	/**
	 * @return self|null
	 */
	public function fetch()
	{

	}

	public function doFoo()
	{
		while ($test = $this->fetch()) {
			$test->fetch();
		}

		if ($test2 = $this->fetch()) {
			$test2->fetch();
		} elseif ($test3 = $this->fetch()) {
			$test3->fetch();
		}
	}

}

class NullCoalesce
{

	/** @var self|null */
	private $foo;

	public function doFoo()
	{
		$this->foo->find() ?? 'bar';

		if ($this->foo->find() ?? 'bar') {

		}

		($this->foo->find() ?? 'bar') ? 'foo' : 'bar';
	}

	/**
	 * @return self|null
	 */
	public function find()
	{

	}

}
