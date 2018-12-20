<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class MissingConstantFromReflectionException extends \PHPStan\AnalysedCodeException
{

	public function __construct(
		string $className,
		string $constantName,
		?string $currentFilename
	)
	{
		parent::__construct(
			sprintf(
				'Constant %s was not found in reflection of class %s - probably the wrong version of class is autoloaded.%s',
				$constantName,
				$className,
				$currentFilename !== null
					? sprintf(' The currently loaded version is at: %s', $currentFilename)
					: ''
			)
		);
	}

}
