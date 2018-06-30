<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class MissingPropertyFromReflectionException extends \PHPStan\AnalysedCodeException
{

	public function __construct(
		string $className,
		string $propertyName,
		?string $currentFilename
	)
	{
		parent::__construct(
			sprintf(
				'Property $%s was not found in reflection of class %s - probably the wrong version of class is autoloaded.%s',
				$propertyName,
				$className,
				$currentFilename !== null
					? sprintf(' The currently loaded version is at: %s', $currentFilename)
					: ''
			)
		);
	}

}
