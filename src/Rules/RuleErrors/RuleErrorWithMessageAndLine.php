<?php declare(strict_types = 1);

namespace PHPStan\Rules\RuleErrors;

use PHPStan\Rules\LineRuleError;

class RuleErrorWithMessageAndLine implements LineRuleError
{

	/** @var string */
	private $message;

	/** @var int */
	private $line;

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
