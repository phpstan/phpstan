<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class Error
{

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var int|NULL
	 */
	private $line;

	public function __construct(string $message, string $file, int $line = null)
	{
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	/**
	 * @return int|NULL
	 */
	public function getLine()
	{
		return $this->line;
	}

}
