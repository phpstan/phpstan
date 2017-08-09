<?php declare(strict_types = 1);

namespace PHPStan\File;

use PHPStan\Cache\Cache;

class ModifiedFilesBasedOnLastModifiedTimeDetector implements ModifiedFilesDetector
{

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var int[] */
	private $files;

	public function __construct(Cache $cache)
	{
		$this->cache = $cache;

		$files = $this->cache->load($this->getCacheKey());
		$this->files = $files !== null ? $files : [];
	}

	/**
	 * @param string[] $files
	 * @return string[]
	 */
	public function getAddedFiles(array $files): array
	{
		return array_diff($files, array_keys($this->files));
	}

	/**
	 * @return string[]
	 */
	public function getChangedFiles(): array
	{
		return array_filter(array_keys($this->files), function (string $file): bool {
			$lastModifiedTime = @filemtime($file);
			return $lastModifiedTime !== false && $lastModifiedTime > $this->files[$file];
		});
	}

	/**
	 * @return string[]
	 */
	public function getRemovedFiles(): array
	{
		return array_filter(array_keys($this->files), function (string $file): bool {
			return !is_file($file);
		});
	}

	/**
	 * @param string[] $files
	 */
	public function updateFiles(array $files)
	{
		$this->files = [];
		foreach ($files as $file) {
			$this->files[$file] = filemtime($file);
		}

		$this->cache->save($this->getCacheKey(), $this->files);
	}

	private function getCacheKey(): string
	{
		return 'fileModifiedTimeMap';
	}

}
