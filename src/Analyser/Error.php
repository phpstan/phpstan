<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class Error
{

	/** @var string */
	private $message;

	/** @var string */
	private $file;

	/** @var int|NULL */
	private $line;

	/** @var bool */
	private $canBeIgnored;

	/** @var string|null */
	private $filePath;

	public function __construct(
		string $message,
		string $file,
		?int $line = null,
		bool $canBeIgnored = true,
		?string $filePath = null
	)
	{
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
		$this->canBeIgnored = $canBeIgnored;
		$this->filePath = $filePath;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getFilePath(): string
	{
		if ($this->filePath === null) {
			return $this->file;
		}

		return $this->filePath;
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
