<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PHPStan\ShouldNotHappenException;

class InternalScopeHelper
{

	/** @var string[] */
	private $internalPaths;

	/** @var string[][] */
	private $pathsPerComposerJson;

	/**
	 * @param string[] $internalPaths
	 */
	public function __construct(array $internalPaths)
	{
		$this->internalPaths = $internalPaths;
	}

	public function isFileInInternalPaths(string $fileName): bool
	{
		$internalPaths = $this->getInternalPaths($fileName);
		$matchedPaths = array_filter($internalPaths, function (string $internalPath) use ($fileName) {
			$fileName = realpath($fileName);
			$internalPath = realpath($internalPath);

			if ($fileName === false || $internalPath === false) {
				return false;
			}

			return strpos($fileName, $internalPath) === 0;
		});

		return count($matchedPaths) > 0;
	}

	private function getInternalPaths(string $fileName): array
	{
		$composerFilePath = $this->getComposerFilePath($fileName);
		if ($composerFilePath === null) {
			throw new ShouldNotHappenException();
		}

		$composerPaths = $this->getComposerPaths($composerFilePath);

		return array_unique(array_merge($this->internalPaths, $composerPaths));
	}

	private function getComposerPaths(string $composerFilePath): array
	{
		if (isset($this->pathsPerComposerJson[$composerFilePath])) {
			return $this->pathsPerComposerJson[$composerFilePath];
		}

		$composerConfig = $this->parseComposerConfig($composerFilePath);

		$psr0 = $composerConfig['autoload']['psr-0'] ?? [];
		$psr4 = $composerConfig['autoload']['psr-4'] ?? [];
		$classmap = $composerConfig['autoload']['classmap'] ?? [];
		$files = $composerConfig['autoload']['files'] ?? [];
		$devPsr0 = $composerConfig['autoload-dev']['psr-0'] ?? [];
		$devPsr4 = $composerConfig['autoload-dev']['psr-4'] ?? [];
		$devClassmap = $composerConfig['autoload-dev']['classmap'] ?? [];
		$devFiles = $composerConfig['autoload-dev']['files'] ?? [];

		$composerPaths = array_merge(
			array_values($psr0),
			array_values($psr4),
			$classmap,
			$files,
			array_values($devPsr0),
			array_values($devPsr4),
			$devClassmap,
			$devFiles
		);

		// Flatten namespaces
		$composerPaths = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($composerPaths)));

		// Make paths absolute
		$composerPaths = array_map(function (string $path) use ($composerFilePath) {
			return dirname($composerFilePath) . '/' . $path;
		}, $composerPaths);

		$this->pathsPerComposerJson[$composerFilePath] = $composerPaths;

		return $composerPaths;
	}

	private function parseComposerConfig(string $composerFilePath): array
	{
		$composerFileContent = file_get_contents($composerFilePath);
		if ($composerFileContent === false) {
			throw new ShouldNotHappenException();
		}

		return json_decode($composerFileContent, true);
	}

	private function getComposerFilePath(string $filePath): ?string
	{
		$dir = dirname($filePath);

		while (true) {
			$composerFile = $dir . '/composer.json';

			if (file_exists($composerFile)) {
				return $composerFile;
			}

			$parentDir = dirname($dir);
			if ($dir === $parentDir) {
				break;
			}

			$dir = $parentDir;
		}

		return null;
	}

}
