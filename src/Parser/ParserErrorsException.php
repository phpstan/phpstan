<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\Error;

class ParserErrorsException extends \Exception
{

	/** @var \PhpParser\Error[] */
	private $errors;

	/**
	 * @param \PhpParser\Error[] $errors
	 */
	public function __construct(array $errors)
	{
		parent::__construct(implode(', ', array_map(static function (Error $error): string {
			return $error->getMessage();
		}, $errors)));
		$this->errors = $errors;
	}

	/**
	 * @return \PhpParser\Error[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
