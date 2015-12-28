<?php declare(strict_types=1);

namespace PHPStan\Reflection\Php;

class DocCommentTypesParseErrorException extends \Exception
{

	public function __construct(\Throwable $previous)
	{
		parent::__construct(
			'Error occured while resolving type names in phpDoc.',
			0,
			$previous
		);
	}

}
