<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

/**
 * @deprecated Use PHPStan\File\RelativePathHelper instead
 */
class RelativePathHelper
{

	public static function getRelativePath(string $currentDirectory, string $filename): string
	{
		if ($currentDirectory !== '' && strpos($filename, $currentDirectory) === 0) {
			return substr($filename, strlen($currentDirectory) + 1);
		}

		return $filename;
	}

}
