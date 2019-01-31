<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Rules\RuleErrors\RuleErrorWithMessage;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndFile;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndLine;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndLineAndFile;

class RuleErrorBuilder
{

	/** @var string */
	private $message;

	/** @var int|null */
	private $line;

	/** @var string|null */
	private $file;

	private function __construct()
	{
	}

	public static function message(string $message): self
	{
		$self = new self();
		$self->message = $message;

		return $self;
	}

	public function line(int $line): self
	{
		$this->line = $line;

		return $this;
	}

	public function file(string $file): self
	{
		$this->file = $file;

		return $this;
	}

	public function build(): RuleError
	{
		if ($this->line !== null && $this->file !== null) {
			return new RuleErrorWithMessageAndLineAndFile($this->message, $this->line, $this->file);
		}
		if ($this->line !== null && $this->file === null) {
			return new RuleErrorWithMessageAndLine($this->message, $this->line);
		}
		if ($this->line === null && $this->file !== null) {
			return new RuleErrorWithMessageAndFile($this->message, $this->file);
		}

		return new RuleErrorWithMessage($this->message);
	}

}
