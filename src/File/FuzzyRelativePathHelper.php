<?php declare(strict_types = 1);

namespace PHPStan\File;

class FuzzyRelativePathHelper implements RelativePathHelper
{

	/** @var string */
	private $directorySeparator;

	/** @var string|null */
	private $pathToTrim;

	/**
	 * @param string $currentWorkingDirectory
	 * @param string $directorySeparator
	 * @param string[] $analysedPaths
	 */
	public function __construct(
		string $currentWorkingDirectory,
		string $directorySeparator,
		array $analysedPaths
	)
	{
		$this->directorySeparator = $directorySeparator;
		$pathBeginning = null;
		$pathToTrimArray = null;
		$trimBeginning = static function (string $path): array {
			if (substr($path, 0, 1) === '/') {
				return [
					'/',
					substr($path, 1),
				];
			} elseif (substr($path, 1, 1) === ':') {
				return [
					substr($path, 0, 3),
					substr($path, 3),
				];
			}

			return ['', $path];
		};

		if (
			!in_array($currentWorkingDirectory, ['', '/'], true)
			&& !(strlen($currentWorkingDirectory) === 3 && substr($currentWorkingDirectory, 1, 1) === ':')
		) {
			[$pathBeginning, $currentWorkingDirectory] = $trimBeginning($currentWorkingDirectory);

			/** @var string[] $pathToTrimArray */
			$pathToTrimArray = explode($directorySeparator, $currentWorkingDirectory);
		}
		foreach ($analysedPaths as $pathNumber => $path) {
			[$tempPathBeginning, $path] = $trimBeginning($path);

			/** @var string[] $pathArray */
			$pathArray = explode($directorySeparator, $path);
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

			$pathBeginning = $tempPathBeginning;
			$pathToTrimArray = $pathTempParts;
		}

		if ($pathToTrimArray === null || count($pathToTrimArray) === 0) {
			return;
		}

		$pathToTrim = $pathBeginning . implode($directorySeparator, $pathToTrimArray);
		$realPathToTrim = realpath($pathToTrim);
		if ($realPathToTrim !== false) {
			$pathToTrim = $realPathToTrim;
		}

		$this->pathToTrim = $pathToTrim;
	}

	public function getRelativePath(string $filename): string
	{
		if (
			$this->pathToTrim !== null
			&& strpos($filename, $this->pathToTrim) === 0
		) {
			return ltrim(substr($filename, strlen($this->pathToTrim)), $this->directorySeparator);
		}

		return $filename;
	}

}
