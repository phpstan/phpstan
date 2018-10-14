<?php declare(strict_types = 1);

namespace PHPStan\File;

use const DIRECTORY_SEPARATOR;
use function explode;

class RelativePathHelper
{

	/** @var string|null */
	private $pathToTrim;

	/**
	 * @param string $currentWorkingDirectory
	 * @param string[] $analysedPaths
	 */
	public function __construct(
		string $currentWorkingDirectory,
		array $analysedPaths
	)
	{
		$pathToTrimArray = null;
		if (!in_array($currentWorkingDirectory, ['', '/'], true)) {
			$pathToTrimArray = explode(DIRECTORY_SEPARATOR, trim($currentWorkingDirectory, DIRECTORY_SEPARATOR));
		}
		foreach ($analysedPaths as $pathNumber => $path) {
			$pathArray = explode(DIRECTORY_SEPARATOR, trim($path, DIRECTORY_SEPARATOR));
			$pathTempParts = [];
			foreach ($pathArray as $i => $pathPart) {
				if (\Nette\Utils\Strings::endsWith($pathPart, '.php')) {
					continue;
				}
				if (!isset($pathToTrimArray[$i])) {
					if ($pathNumber !== 0) {
						$pathToTrimArray = $pathTempParts;
						continue 2;
					}
				} elseif ($pathToTrimArray[$i] !== $pathPart) {
					$pathToTrimArray = $pathTempParts;
					continue 2;
				}

				$pathTempParts[] = $pathPart;
			}

			$pathToTrimArray = $pathTempParts;
		}

		if ($pathToTrimArray === null || count($pathToTrimArray) === 0) {
			return;
		}

		$this->pathToTrim = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathToTrimArray);
	}

	public function getRelativePath(string $filename): string
	{
		if (
			$this->pathToTrim !== null
			&& strpos($filename, $this->pathToTrim) === 0
		) {
			return ltrim(substr($filename, strlen($this->pathToTrim)), DIRECTORY_SEPARATOR);
		}

		return $filename;
	}

}
