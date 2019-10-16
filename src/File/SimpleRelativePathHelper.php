<?php declare(strict_types = 1);

namespace PHPStan\File;

class SimpleRelativePathHelper implements RelativePathHelper
{

	/** @var string */
	private $currentWorkingDirectory;

	public function __construct(string $currentWorkingDirectory, string $directorySeparator = DIRECTORY_SEPARATOR)
	{
		$this->currentWorkingDirectory = rtrim($currentWorkingDirectory, $directorySeparator);
		if ($currentWorkingDirectory === '') {
			return;
		}
		$this->currentWorkingDirectory .= $directorySeparator;
	}

	public function getRelativePath(string $filename): string
	{
		if ($this->currentWorkingDirectory !== '' && strpos($filename, $this->currentWorkingDirectory) === 0) {
			return substr($filename, strlen($this->currentWorkingDirectory));
		}

		return $filename;
	}

}
