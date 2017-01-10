<?php declare(strict_types = 1);

namespace PHPStan;

class FileHelper
{

	public function isAbsolutePath(string $path): bool
	{
		return substr($path, 0, 1) === '/' || substr($path, 1, 1) === ':';
	}

	public function normalizePath(string $path): string
	{
		$path = str_replace('\\', '/', $path);
		$path = preg_replace('~/{2,}~', '/', $path);

		$pathRoot = strpos($path, '/') === 0 ? DIRECTORY_SEPARATOR : '';
		$pathParts = explode('/', trim($path, '/'));

		$normalizedPathParts = [];
		foreach ($pathParts as $pathPart) {
			if ($pathPart === '.') {
				continue;
			}
			if ($pathPart === '..') {
				array_pop($normalizedPathParts);
			} else {
				$normalizedPathParts[] = $pathPart;
			}
		}

		return $pathRoot . implode(DIRECTORY_SEPARATOR, $normalizedPathParts);
	}

}
