<?php declare(strict_types = 1);

namespace PHPStan\File;

interface ModifiedFilesDetector
{

	/**
	 * @param string[] $files
	 * @return string[]
	 */
	public function getAddedFiles(array $files): array;

	/**
	 * @return string[]
	 */
	public function getChangedFiles(): array;

	/**
	 * @return string[]
	 */
	public function getRemovedFiles(): array;

	/**
	 * @param string[] $files
	 */
	public function updateFiles(array $files);

}
