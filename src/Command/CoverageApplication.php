<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\CoverageReporter;
use PHPStan\Analyser\Error;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Finder\Finder;

class CoverageApplication
{

	/**
	 * @var \PHPStan\Analyser\CoverageReporter
	 */
	private $coverageReporter;

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

	public function __construct(
		CoverageReporter $coverageReporter,
		string $memoryLimitFile,
		FileHelper $fileHelper,
		array $fileExtensions
	)
	{
		$this->coverageReporter = $coverageReporter;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->fileExtensions = $fileExtensions;
		$this->fileHelper = $fileHelper;
	}

	/**
	 * @param string[] $paths
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @return int Error code.
	 */
	public function coverage(
		array $paths,
		OutputStyle $style
	): int
	{
		$errors = [];
		$files = [];

		$this->updateMemoryLimitFile();

		$paths = array_map(function (string $path): string {
			return $this->fileHelper->absolutizePath($path);
		}, $paths);

		foreach ($paths as $path) {
			if (!file_exists($path)) {
				throw new \RuntimeException(sprintf('<error>Path %s does not exist</error>', $path));
			} elseif (is_file($path)) {
				$files[] = $this->fileHelper->normalizePath($path);
			} else {
				$finder = new Finder();
				$finder->followLinks();
				foreach ($finder->files()->name('*.{' . implode(',', $this->fileExtensions) . '}')->in($path) as $fileInfo) {
					$files[] = $this->fileHelper->normalizePath($fileInfo->getPathname());
				}
			}
		}

		$this->updateMemoryLimitFile();

		$progressStarted = false;

		$fileOrder = 0;
		$reports = $this->coverageReporter->analyse(
			$files,
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
		);

		if ($progressStarted) {
			$style->progressFinish();
		}

		$f = $style->getFormatter();
		foreach ($reports as $filePath => $warningMap) {
			$style->writeln('-- ' . $filePath);

			$handle = fopen($filePath, 'r');
			if ($handle) {
				$lineCounter = 1;
				while (($line = fgets($handle)) !== false) {
					$hasWarnings = isset($warningMap[$lineCounter]);

					if ($hasWarnings) {
						$style->writeln('<question>-  ' . $f->escape(rtrim($line)) . '</>');

						foreach ($warningMap[$lineCounter] as $warning) {
							$style->writeln('<info>? ' . $f->escape($warning) . '</>');
						}
					} else {
						$style->write('+  ' . $f->escape($line));
					}
					$lineCounter++;
				}

				fclose($handle);
			} else {
				throw new \RuntimeException('Could not open file: ' . $filePath);
			}
		}

		return 0;
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
