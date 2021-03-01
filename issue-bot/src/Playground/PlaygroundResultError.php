<?php declare(strict_types = 1);

namespace App\Playground;

class PlaygroundResultError
{

	private string $message;

	private int $line;

	public function __construct(string $message, int $line)
	{
		$this->message = $message;
		$this->line = $line;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getLine(): int
	{
		return $this->line;
	}

}
