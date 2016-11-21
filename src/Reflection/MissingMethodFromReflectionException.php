<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class MissingMethodFromReflectionException extends \PHPStan\AnalysedCodeException
{

	public function __construct(string $className, string $methodName)
	{
		parent::__construct(
			sprintf(
				'Method %s() was not found in reflection of class %s - probably the wrong version of class is autoloaded.',
				$methodName,
				$className
			)
		);
	}

}
