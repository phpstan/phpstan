<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Finder\Finder;

class AnalyseApplication
{

	/**
	 * @var \PHPStan\Analyser\Analyser
	 */
	private $analyser;

	public function __construct(Analyser $analyser)
	{
		$this->analyser = $analyser;
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

		foreach ($paths as $path) {
			$realpath = realpath($path);
			if ($realpath === false || !file_exists($realpath)) {
				$errors[] = new Error(sprintf('<error>Path %s does not exist</error>', $path), $path);
			} elseif (is_file($realpath)) {
				$files[] = $realpath;
			} else {
				$finder = new Finder();
				foreach ($finder->files()->name('*.php')->in($realpath) as $fileInfo) {
					$files[] = $fileInfo->getPathname();
				}
			}
		}

		$progressStarted = false;

		$errors = array_merge($errors, $this->analyser->analyse(
			$files,
			function () use ($style, &$progressStarted, $files) {
				if (!$progressStarted) {
					$style->progressStart(count($files));
					$progressStarted = true;
				}
				$style->progressAdvance();
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

		$currentDir = realpath(dirname($paths[0]));
		$cropFilename = function ($filename) use ($currentDir) {
			if ($currentDir !== false && strpos($filename, $currentDir) === 0) {
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

}
