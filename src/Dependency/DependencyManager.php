<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PHPStan\Cache\Cache;

class DependencyManager
{

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var string[][] */
	private $dependencies = [];

	/** @var string[][]|null */
	private $reverseDependencies;

	public function __construct(Cache $cache)
	{
		$this->cache = $cache;

		$dependencies = $this->cache->load($this->getCacheKey());
		if ($dependencies !== null) {
			$this->dependencies = $this->unpack($dependencies);
		}
	}

	/**
	 * @param string[] $files
	 */
	public function updateFiles(array $files)
	{
		foreach (array_diff(array_keys($this->dependencies), $files) as $fileToRemove) {
			unset($this->dependencies[$fileToRemove]);
		}
	}

	/**
	 * @param string $file
	 * @param string[] $fileDependencies
	 */
	public function setFileDependencies(string $file, array $fileDependencies)
	{
		$this->dependencies[$file] = $fileDependencies;
	}

	/**
	 * @param string $searchedFile
	 * @return string[]
	 */
	public function getFilesDependentOn(string $searchedFile): array
	{
		if ($this->reverseDependencies === null) {
			$this->reverseDependencies = [];
			foreach ($this->dependencies as $file => $fileDependencies) {
				foreach ($fileDependencies as $fileDependency) {
					if (!array_key_exists($fileDependency, $this->reverseDependencies)) {
						$this->reverseDependencies[$fileDependency] = [];
					}
					if (!in_array($file, $this->reverseDependencies[$fileDependency], true)) {
						$this->reverseDependencies[$fileDependency][] = $file;
					}
				}
			}
		}

		return array_key_exists($searchedFile, $this->reverseDependencies) ? $this->reverseDependencies[$searchedFile] : [];
	}

	/**
	 * @return string[][]
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	public function saveToCache()
	{
		$this->cache->save($this->getCacheKey(), $this->pack($this->dependencies));
	}

	private function pack(array $dependencies): array
	{
		$files = [];
		foreach ($dependencies as $file => $fileDependencies) {
			$files[$file] = $file;
			foreach ($fileDependencies as $fileDependency) {
				$files[$fileDependency] = $fileDependency;
			}
		}
		$files = array_values($files);
		$filesNumbers = array_flip($files);

		$packedDependencies = [];
		foreach ($dependencies as $file => $fileDependencies) {
			$packedFileDependencies = [];
			foreach ($fileDependencies as $fileDependency) {
				$packedFileDependencies[] = $filesNumbers[$fileDependency];
			}
			if (count($packedFileDependencies) > 0) {
				$packedDependencies[$filesNumbers[$file]] = $packedFileDependencies;
			}
		}

		return [$files, $packedDependencies];
	}

	private function unpack(array $data): array
	{
		list($files, $packedDependencies) = $data;

		$dependencies = [];
		foreach ($files as $fileNumber => $file) {
			$dependencies[$file] = [];
			if (array_key_exists($fileNumber, $packedDependencies)) {
				foreach ($packedDependencies[$fileNumber] as $fileDependencyNumber) {
					$dependencies[$file][] = $files[$fileDependencyNumber];
				}
			}
		}

		return $dependencies;
	}

	private function getCacheKey(): string
	{
		return 'dependencyMap';
	}

}
