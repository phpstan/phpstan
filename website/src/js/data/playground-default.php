<?php declare(strict_types = 1);

use function PHPStan\dumpType;
use function PHPStan\Testing\assertType;

class CoffeeBreak
{
	public function getDuration(): int { }
}

class MondayMorning
{
	private bool $coffeeConsumed = false;

	public function startDay(string|int $task): string
	{
		$this->coffeeConsumed = true;
		
		/** @var DateTime $deadline */
		$deadline = new DateTimeImmutable('friday');
		$deadline->modify('-1 week'); // that will help
		
		$words = count(explode(' ', $task));
		if ($words === 0) { echo 'if only'; }
		$this->deployToProduction();
		return array_pop($task);
	}
}

$cb = new CoffeeBreak();
if (isset($cb->getDuration())) { echo 'break time'; }

echo sprintf('%s %s', 'safe');
