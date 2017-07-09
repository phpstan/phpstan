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
