<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Finder\Finder;

class AnalyseApplication
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	/** @var string */
	private $memoryLimitFile;

	/** @var string[] */
	private $fileExtensions;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var \PHPStan\File\FileExcluder */
	private $fileExcluder;

	/**
	 * @param Analyser $analyser
	 * @param string $memoryLimitFile
	 * @param FileHelper $fileHelper
	 * @param string[] $fileExtensions
	 * @param FileExcluder $fileExcluder
	 */
	public function __construct(
		Analyser $analyser,
		string $memoryLimitFile,
		FileHelper $fileHelper,
		array $fileExtensions,
		FileExcluder $fileExcluder
	)
	{
		$this->analyser = $analyser;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->fileExtensions = $fileExtensions;
		$this->fileHelper = $fileHelper;
		$this->fileExcluder = $fileExcluder;
	}

	/**
	 * @param string[] $paths
	 * @param \Symfony\Component\Console\Style\OutputStyle $consoleStyle
	 * @param \Symfony\Component\Console\Style\OutputStyle $reportStyle
	 * @param \PHPStan\Command\ErrorFormatter\ErrorFormatter $errorFormatter
	 * @param bool $defaultLevelUsed
	 * @param bool $debug
	 * @return int Error code.
	 */
	public function analyse(
		array $paths,
		OutputStyle $consoleStyle,
		OutputStyle $reportStyle,
		ErrorFormatter $errorFormatter,
		bool $defaultLevelUsed,
		bool $debug
	): int
	{
		if (count($paths) === 0) {
			throw new \InvalidArgumentException('At least one path must be specified to analyse.');
		}

		$errors = [];
		$files = [];

		$this->updateMemoryLimitFile();

		$paths = array_map(function (string $path): string {
			return $this->fileHelper->absolutizePath($path);
		}, $paths);

		$onlyFiles = true;
		foreach ($paths as $path) {
			if (!file_exists($path)) {
				$errors[] = new Error(sprintf('<error>Path %s does not exist</error>', $path), $path, null, false);
			} elseif (is_file($path)) {
				$files[] = $this->fileHelper->normalizePath($path);
			} else {
				$finder = new Finder();
				$finder->followLinks();
				foreach ($finder->files()->name('*.{' . implode(',', $this->fileExtensions) . '}')->in($path) as $fileInfo) {
					$files[] = $this->fileHelper->normalizePath($fileInfo->getPathname());
					$onlyFiles = false;
				}
			}
		}

		$files = array_filter($files, function (string $file): bool {
			return !$this->fileExcluder->isExcludedFromAnalysing($file);
		});

		$this->updateMemoryLimitFile();

		if (!$debug) {
			$progressStarted = false;
			$fileOrder = 0;
			$preFileCallback = null;
			$postFileCallback = function () use ($consoleStyle, &$progressStarted, $files, &$fileOrder): void {
				if (!$progressStarted) {
					$consoleStyle->progressStart(count($files));
					$progressStarted = true;
				}
				$consoleStyle->progressAdvance();
				if ($fileOrder % 100 === 0) {
					$this->updateMemoryLimitFile();
				}
				$fileOrder++;
			};
		} else {
			$preFileCallback = function (string $file) use ($consoleStyle): void {
				$consoleStyle->writeln($file);
			};
			$postFileCallback = null;
		}

		$errors = array_merge($errors, $this->analyser->analyse(
			$files,
			$onlyFiles,
			$preFileCallback,
			$postFileCallback,
			$debug
		));

		if (isset($progressStarted) && $progressStarted) {
			$consoleStyle->progressFinish();
		}

		$fileSpecificErrors = [];
		$notFileSpecificErrors = [];
		foreach ($errors as $error) {
			if (is_string($error)) {
				$notFileSpecificErrors[] = $error;
			} else {
				$fileSpecificErrors[] = $error;
			}
		}

		return $errorFormatter->formatErrors(
			new AnalysisResult(
				$fileSpecificErrors,
				$notFileSpecificErrors,
				$defaultLevelUsed,
				$this->fileHelper->normalizePath(dirname($paths[0]))
			),
			$reportStyle
		);
	}

	private function updateMemoryLimitFile(): void
	{
		$bytes = memory_get_peak_usage(true);
		$megabytes = ceil($bytes / 1024 / 1024);
		file_put_contents($this->memoryLimitFile, sprintf('%d MB', $megabytes));

		if (!function_exists('pcntl_signal_dispatch')) {
			return;
		}

		pcntl_signal_dispatch();
	}

}
