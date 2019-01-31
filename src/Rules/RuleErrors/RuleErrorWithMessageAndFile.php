<?php declare(strict_types = 1);

namespace PHPStan\Rules\RuleErrors;

use PHPStan\Rules\FileRuleError;

class RuleErrorWithMessageAndFile implements FileRuleError
{

	/** @var string */
	private $message;

	/** @var string */
	private $file;

	public function __construct(string $message, string $file)
	{
		$this->message = $message;
		$this->file = $file;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getFile(): string
	{
		return $this->file;
	}

}
