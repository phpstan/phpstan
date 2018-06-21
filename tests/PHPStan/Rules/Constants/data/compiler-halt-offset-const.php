<?php declare(strict_types = 1);

function greet(string $name): string
{
	$template = file_get_contents(__FILE__, false, null, __COMPILER_HALT_OFFSET__);

	return strtr($template, [
		'%name%' => $name,
	]);
}

echo greet('Bob');

__halt_compiler();
Hello, %name%!
