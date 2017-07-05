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

}
