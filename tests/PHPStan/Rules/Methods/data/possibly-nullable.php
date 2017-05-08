<?php

namespace PossiblyNullable;

class Test {

	/** @var \DateTimeImmutable|null */
	private $date;

	public function __construct() {
		$this->date->format('Y');

		if (!is_null($this->date)) {
			$this->date->format('Y');
		}
	}

}
