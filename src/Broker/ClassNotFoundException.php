<?php declare(strict_types = 1);

namespace PHPStan\Broker;

class ClassNotFoundException extends \PHPStan\AnalysedCodeException
{

	/** @var string */
	private $className;

	public function __construct(string $functionName)
	{
		parent::__construct(sprintf('Class %s not found.', $functionName));
		$this->className = $functionName;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

}
