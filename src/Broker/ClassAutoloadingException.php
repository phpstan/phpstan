<?php declare(strict_types = 1);

namespace PHPStan\Broker;

class ClassAutoloadingException extends \PHPStan\AnalysedCodeException
{

	/** @var string */
	private $className;

	public function __construct(
		string $functionName,
		\Throwable $previous
	)
	{
		parent::__construct(sprintf(
			'%s (%s) thrown while autoloading class %s.',
			get_class($previous),
			$previous->getMessage(),
			$functionName
		), 0, $previous);
		$this->className = $functionName;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

}
