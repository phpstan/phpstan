<?php declare(strict_types = 1);

var_dump(str_contains('test', 't'));

function (\Stringable $s): void {
	echo (string) $s;
};

class Foo implements \Stringable
{

	public function __toString()
	{
		return 'foo';
	}

}
