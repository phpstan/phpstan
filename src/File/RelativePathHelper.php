<?php declare(strict_types = 1);

namespace PHPStan\File;

class RelativePathHelper
{

	/** @var string */
	private $pathToTrim;

	public function __construct(
		string $currentWorkingDirectory
	)
	{
		$this->pathToTrim = $currentWorkingDirectory;
	}

	public function getRelativePath(string $filename): string
	{
		if ($this->pathToTrim !== '' && strpos($filename, $this->pathToTrim) === 0) {
			return substr($filename, strlen($this->pathToTrim) + 1);
		}

		return $filename;
	}

}
