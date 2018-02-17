<?php

namespace AccessingPropertiesOnPossiblyNull;

class Test {

	/** @var \DateTimeImmutable|null */
	private $date;

	public function __construct() {
		$this->date->foo;

		if (!is_null($this->date)) {
			$this->date->foo;
		}
	}

	public function doFoo()
	{
		if ($this->date !== null) {
			return;
		}

		if (something()) {
			$this->date = 'foo';
		} else {
			$this->date = 1;
		}

		echo $this->date->foo; // is surely string|int
	}

}

class Foo
{

	/** @var Test */
	public $ipsum;

}

class CheckingPropertyNotNullInIfCondition
{

	public function doFoo()
	{
		/** @var Foo|null $foo */
		$foo = doFoo();

		/** @var Foo|null $bar */
		$bar = doFoo();
		if (null !== $foo ? $foo->ipsum : false) {

		} elseif ($bar !== null ? $bar->ipsum : false) {

		}
	}

}

class NullCoalesce
{

	/** @var self|null */
	private $foo;

	public function doFoo()
	{
		$this->foo->foo ?? 'bar';

		if ($this->foo->foo ?? 'bar') {

		}

		($this->foo->foo ?? 'bar') ? 'foo' : 'bar';

		if (isset($this->foo->foo)) {
			$this->foo->foo;
			$this->foo->foo->foo;
		}

		isset($this->foo->foo) ? $this->foo->foo : null;
		isset($this->foo->foo) ? $this->foo->foo->foo : null;

		if (!empty($this->foo->foo)) {
			$this->foo->foo;
			$this->foo->foo->foo;
		}

		!empty($this->foo->foo) ? $this->foo->foo : null;
		!empty($this->foo->foo) ? $this->foo->foo->foo : null;
	}

}
