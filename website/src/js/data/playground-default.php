<?php declare(strict_types = 1);

use function PHPStan\dumpType;
use function PHPStan\Testing\assertType;

class Greeter
{
	public function greet(string $name): string
	{
		return 'Hello, ' . $name;
	}
}

class LoudGreeter extends Greeter
{
	#[\Override]
	public function greet(string $name): string
	{
		return strtoupper(parent::greet($name)) . '!';
	}

	#[\Override]
	public function wave(): string
	{
		return 'o/';
	}
}

$greeter = new LoudGreeter();
echo $greeter->greet('world'); // @phpstan-ignore argument.type
echo $greeter->greet(42);
