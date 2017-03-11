<?php declare(strict_types = 1);

namespace PHPStan\Command;

class AnalysisResult
{

	/**
	 * @var \PHPStan\Analyser\Error[]
	 */
	private $fileSpecificErrors;

	/**
	 * @var string[]
	 */
	private $notFileSpecificErrors;

	/**
	 * @var bool
	 */
	private $defaultLevelUsed;

	/**
	 * @var string
	 */
	private $currentDirectory;

	/**
	 * @param \PHPStan\Analyser\Error[] $fileSpecificErrors
	 * @param string[] $notFileSpecificErrors
	 * @param bool $defaultLevelUsed
	 * @param string $currentDirectory
	 */
	public function __construct(
		array $fileSpecificErrors,
		array $notFileSpecificErrors,
		bool $defaultLevelUsed,
		string $currentDirectory
	)
	{
		$this->fileSpecificErrors = $fileSpecificErrors;
		$this->notFileSpecificErrors = $notFileSpecificErrors;
		$this->defaultLevelUsed = $defaultLevelUsed;
		$this->currentDirectory = $currentDirectory;
	}

	public function hasErrors(): bool
	{
		return $this->getTotalErrorsCount() > 0;
	}

	public function getTotalErrorsCount(): int
	{
		return count($this->fileSpecificErrors) + count($this->notFileSpecificErrors);
	}

	/**
	 * @return \PHPStan\Analyser\Error[]
	 */
	public function getFileSpecificErrors(): array
	{
		return $this->fileSpecificErrors;
	}

	/**
	 * @return string[]
	 */
	public function getNotFileSpecificErrors(): array
	{
		return $this->notFileSpecificErrors;
	}

	public function isDefaultLevelUsed(): bool
	{
		return $this->defaultLevelUsed;
	}

	public function getCurrentDirectory(): string
	{
		return $this->currentDirectory;
	}

}
