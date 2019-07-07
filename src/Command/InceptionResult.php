<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class InceptionResult
{

	/** @var string[] */
	private $files;

	/** @var bool */
	private $onlyFiles;

	/** @var OutputStyle */
	private $consoleStyle;

	/** @var OutputInterface */
	private $errorOutput;

	/** @var Container */
	private $container;

	/** @var bool */
	private $isDefaultLevelUsed;

	/** @var string */
	private $memoryLimitFile;

	/** @var string|null */
	private $projectConfigFile;

	/**
	 * @param string[] $files
	 * @param bool $onlyFiles
	 * @param OutputStyle $consoleStyle
	 * @param OutputInterface $errorOutput
	 * @param Container $container
	 * @param bool $isDefaultLevelUsed
	 * @param string $memoryLimitFile
	 * @param string|null $projectConfigFile
	 */
	public function __construct(
		array $files,
		bool $onlyFiles,
		OutputStyle $consoleStyle,
		OutputInterface $errorOutput,
		Container $container,
		bool $isDefaultLevelUsed,
		string $memoryLimitFile,
		?string $projectConfigFile
	)
	{
		$this->files = $files;
		$this->onlyFiles = $onlyFiles;
		$this->consoleStyle = $consoleStyle;
		$this->errorOutput = $errorOutput;
		$this->container = $container;
		$this->isDefaultLevelUsed = $isDefaultLevelUsed;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->projectConfigFile = $projectConfigFile;
	}

	/**
	 * @return string[]
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	public function isOnlyFiles(): bool
	{
		return $this->onlyFiles;
	}

	public function getConsoleStyle(): OutputStyle
	{
		return $this->consoleStyle;
	}

	public function getErrorOutput(): OutputInterface
	{
		return $this->errorOutput;
	}

	public function getContainer(): Container
	{
		return $this->container;
	}

	public function isDefaultLevelUsed(): bool
	{
		return $this->isDefaultLevelUsed;
	}

	public function getProjectConfigFile(): ?string
	{
		return $this->projectConfigFile;
	}

	public function handleReturn(int $exitCode): int
	{
		@unlink($this->memoryLimitFile);
		return $exitCode;
	}

}
