<?php declare(strict_types = 1);

namespace Levels;

class Zero
{

	public function doFoo(int $i)
	{
		$this->doFoo();

		$self = new self();
		$self->doFoo();
	}

	public static function doBar(int $i)
	{
		self::doBar();
		Zero::doBar();

		sprintf('%s %s', 'foo');
	}

	public function doBaz(Nonexistent $baz)
	{
		$baz->foo();
		echo $lorem;

		if (rand(0, 1)) {
			$ipsum = true;
		}

		echo $ipsum;
	}

}
