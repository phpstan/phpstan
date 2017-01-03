<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\FileHelper;
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

	public function __construct(Analyser $analyser, string $memoryLimitFile)
	{
		$this->analyser = $analyser;
		$this->memoryLimitFile = $memoryLimitFile;
	}

	/**
	 * @param string[] $paths
	 * @param \Symfony\Component\Console\Style\StyleInterface $style
	 * @param bool $defaultLevelUsed
	 * @return int
	 */
	public function analyse(array $paths, StyleInterface $style, bool $defaultLevelUsed): int
	{
		$errors = [];
		$files = [];

		$this->updateMemoryLimitFile();

		$workingDirectory = getcwd();
		$paths = array_map(function (string $path) use ($workingDirectory): string {
			return !FileHelper::isAbsolutePath($path) ? $workingDirectory . DIRECTORY_SEPARATOR . $path : $path;
		}, $paths);

		foreach ($paths as $path) {
			if (!file_exists($path)) {
				$errors[] = new Error(sprintf('<error>Path %s does not exist</error>', $path), $path);
			} elseif (is_file($path)) {
				$files[] = $path;
			} else {
				$finder = new Finder();
				foreach ($finder->files()->name('*.php')->in($path) as $fileInfo) {
					$files[] = $fileInfo->getPathname();
				}
			}
		}

		$this->updateMemoryLimitFile();

		$progressStarted = false;

		$fileOrder = 0;
		$errors = array_merge($errors, $this->analyser->analyse(
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
		));

		if ($progressStarted) {
			$style->progressFinish();
		}

		if (count($errors) === 0) {
			$style->success('No errors');
			if ($defaultLevelUsed) {
				$style->note(sprintf(
					'PHPStan is performing only the most basic checks. You can pass a higher rule level through the --%s option (the default and current level is %d) to analyse code more thoroughly.',
					AnalyseCommand::OPTION_LEVEL,
					AnalyseCommand::DEFAULT_LEVEL
				));
			}
			return 0;
		}

		$currentDir = FileHelper::normalizePath(dirname($paths[0]));
		$cropFilename = function (string $filename) use ($currentDir): string {
			if ($currentDir !== '' && strpos($filename, $currentDir) === 0) {
				return substr($filename, strlen($currentDir) + 1);
			}

			return $filename;
		};

		$fileErrors = [];
		$notFileSpecificErrors = [];
		$totalErrorsCount = count($errors);

		foreach ($errors as $error) {
			if (is_string($error)) {
				$notFileSpecificErrors[] = [$error];
				continue;
			}
			if (!isset($fileErrors[$error->getFile()])) {
				$fileErrors[$error->getFile()] = [];
			}

			$fileErrors[$error->getFile()][] = $error;
		}

		foreach ($fileErrors as $file => $errors) {
			$rows = [];
			foreach ($errors as $error) {
				$rows[] = [
					(string) $error->getLine(),
					$error->getMessage(),
				];
			}

			$style->table(['Line', $cropFilename($file)], $rows);
		}

		if (count($notFileSpecificErrors) > 0) {
			$style->table(['Error'], $notFileSpecificErrors);
		}

		$style->error(sprintf($totalErrorsCount === 1 ? 'Found %d error' : 'Found %d errors', $totalErrorsCount));

		return 1;
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
