<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class InceptionResult
{

	/** @var string[] */
	private $paths;

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

	/**
	 * @param string[] $paths
	 * @param OutputStyle $consoleStyle
	 * @param OutputInterface $errorOutput
	 * @param Container $container
	 * @param bool $isDefaultLevelUsed
	 * @param string $memoryLimitFile
	 */
	public function __construct(
		array $paths,
		OutputStyle $consoleStyle,
		OutputInterface $errorOutput,
		Container $container,
		bool $isDefaultLevelUsed,
		string $memoryLimitFile
	)
	{
		$this->paths = $paths;
		$this->consoleStyle = $consoleStyle;
		$this->errorOutput = $errorOutput;
		$this->container = $container;
		$this->isDefaultLevelUsed = $isDefaultLevelUsed;
		$this->memoryLimitFile = $memoryLimitFile;
	}

	/**
	 * @return string[]
	 */
	public function getPaths(): array
	{
		return $this->paths;
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

	public function handleReturn(int $exitCode): int
	{
		@unlink($this->memoryLimitFile);
		return $exitCode;
	}

}
