<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class Error
{

	/** @var string */
	private $message;

	/** @var string */
	private $file;

	/** @var string */
	private $source;

	/** @var int|NULL */
	private $line;

	/** @var bool */
	private $canBeIgnored;

	public function __construct(string $message, string $file, string $source, ?int $line = null, bool $canBeIgnored = true)
	{
		$this->message = $message;
		$this->file = $file;
		$this->source = str_replace('\\', '.', $source);
		$this->line = $line;
		$this->canBeIgnored = $canBeIgnored;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getSource(): string
	{
		return $this->source;
	}

	public function getLine(): ?int
	{
		return $this->line;
	}

	public function canBeIgnored(): bool
	{
		return $this->canBeIgnored;
	}

}
