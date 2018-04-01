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

	public function doWhile()
	{
		$foo = $this;
		while ($foo->fetch() !== null) {
			$foo->fetch()->doFoo();
			$foo = $foo->fetch();
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

class IssetIssue
{

	public function doFoo()
	{
		$one = $this->getNullable();
		$two = $this->getNullable();
		$three = $this->getNullable();

		isset($one);
		isset($two) ? $two->doFoo() : false;
		$array = [
			isset($three) ? $three->doFoo() : false,
		];

		$one->doFoo();
		$two->doFoo();
		$three->doFoo();

		if ($one and $one->doFoo()) {

		}

		if (!$one or $one->doFoo()) {

		}
	}

	/**
	 * @return self|null
	 */
	public function getNullable()
	{

	}

}

class CallArrayKeyAfterAssigningToIt
{

	public function test()
	{
		$arr = [null, null];
		$arr[1] = new \DateTime();
		$arr[1]->add(new \DateInterval('P1D'));

		if (doFoo()) {
			$arr[0] = new \DateTime();
		}

		$arr[0]->add(new \DateInterval('P1D'));
	}

}

class ElseIfTruthyValueBug
{

	public function doFoo(?Test $test)
	{
		if (rand()) {
			$result = new Test();
		} elseif (!is_null($test)) {
			$result = $test;
		} else {
			return;
		}

		$result->fetch();
	}

}
