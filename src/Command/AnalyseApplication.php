<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\File\FileHelper;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Finder\Finder;

class AnalyseApplication
{

	/**
	 * @var \PHPStan\Analyser\Analyser
	 */
	private $analyser;

	/**
	 * @var string
	 */
	private $memoryLimitFile;

	/**
	 * @var string[]
	 */
	private $fileExtensions;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var \PHPStan\Command\ErrorFormatter\ErrorFormatter */
	private $errorFormatter;

	public function __construct(
		Analyser $analyser,
		string $memoryLimitFile,
		FileHelper $fileHelper,
		array $fileExtensions,
		ErrorFormatter $errorFormatter
	)
	{
		$this->analyser = $analyser;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->fileExtensions = $fileExtensions;
		$this->fileHelper = $fileHelper;
		$this->errorFormatter = $errorFormatter;
	}

	/**
	 * @param string[] $paths
	 * @param \Symfony\Component\Console\Style\StyleInterface $style
	 * @param bool $defaultLevelUsed
	 * @param ErrorFormatter $formatter
	 * @return int Error code.
	 */
	public function analyse(array $paths, StyleInterface $style, bool $defaultLevelUsed): int
	{
		$errors = [];
		$files = [];

		$this->updateMemoryLimitFile();

		$paths = array_map(function (string $path): string {
			return $this->fileHelper->absolutizePath($path);
		}, $paths);

		$onlyFiles = true;
		foreach ($paths as $path) {
			if (!file_exists($path)) {
				$errors[] = new Error(sprintf('<error>Path %s does not exist</error>', $path), $path);
			} elseif (is_file($path)) {
				$files[] = $path;
			} else {
				$finder = new Finder();
				foreach ($finder->files()->name('*.{' . implode(',', $this->fileExtensions) . '}')->in($path) as $fileInfo) {
					$files[] = $fileInfo->getPathname();
					$onlyFiles = false;
				}
			}
		}

		$this->updateMemoryLimitFile();

		$progressStarted = false;

		$fileOrder = 0;
		$errors = array_merge($errors, $this->analyser->analyse(
			$files,
			$onlyFiles,
			function () use ($style, &$progressStarted, $files, &$fileOrder) {
				if (!$progressStarted) {
					$style->progressStart(count($files));
					$progressStarted = true;
				}
				$style->progressAdvance();
				if ($fileOrder % 100 === 0) {
					$this->updateMemoryLimitFile();
				}
				$fileOrder++;
			}
		));

		if ($progressStarted) {
			$style->progressFinish();
		}

		return $this->errorFormatter->formatErrors($errors, $paths, $style);
	}

	private function updateMemoryLimitFile()
	{
		$bytes = memory_get_peak_usage(true);
		$megabytes = ceil($bytes / 1024 / 1024);
		file_put_contents($this->memoryLimitFile, sprintf('%d MB', $megabytes));

		if (function_exists('pcntl_signal_dispatch')) {
			pcntl_signal_dispatch();
		}
	}

}
