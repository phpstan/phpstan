<?php

class A
{

	public function fooMethod()
	{
		\Closure::bind(function () {
			$this->fooMethod();
			$this->barMethod();
		}, new A(), self::class);

		$this->fooMethod();
		$this->barMethod();

		\Closure::bind(function () {
			$this->fooMethod();
			$this->barMethod();
		}, $nonexistent, self::class);
	}

}
