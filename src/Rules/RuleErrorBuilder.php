<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Rules\RuleErrors\RuleErrorWithMessage;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndLine;

class RuleErrorBuilder
{

	/** @var string */
	private $message;

	/** @var int|null */
	private $line;

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

	public function build(): RuleError
	{
		if ($this->line !== null) {
			return new RuleErrorWithMessageAndLine($this->message, $this->line);
		}

		return new RuleErrorWithMessage($this->message);
	}

}
