<?php declare(strict_types = 1);

namespace PHPStan\Rules\RuleErrors;

use PHPStan\Rules\FileRuleError;
use PHPStan\Rules\LineRuleError;

class RuleErrorWithMessageAndLineAndFile implements LineRuleError, FileRuleError
{

	/** @var string */
	private $message;

	/** @var int */
	private $line;

	/** @var string */
	private $file;

	public function __construct(string $message, int $line, string $file)
	{
		$this->message = $message;
		$this->line = $line;
		$this->file = $file;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getLine(): int
	{
		return $this->line;
	}

	public function getFile(): string
	{
		return $this->file;
	}

}
