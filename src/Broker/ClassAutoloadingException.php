<?php declare(strict_types = 1);

namespace PHPStan\Broker;

class ClassAutoloadingException extends \PHPStan\AnalysedCodeException
{

	/** @var string */
	private $className;

	public function __construct(
		string $functionName,
		\Throwable $previous = null
	)
	{
		if ($previous !== null) {
			parent::__construct(sprintf(
				'%s (%s) thrown while autoloading class %s.',
				get_class($previous),
				$previous->getMessage(),
				$functionName
			), 0, $previous);
		} else {
			parent::__construct(sprintf(
				'Class %s not found and could not be autoloaded.',
				$functionName
			), 0);
		}

		$this->className = $functionName;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

}
