<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\File\FileHelper;
use PHPStan\Command\AnalyseCommand;

use Symfony\Component\Console\Style\StyleInterface;

class TableErrorFormatter implements ErrorFormatter
{

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	public function __construct(FileHelper $fileHelper)
	{
		$this->fileHelper = $fileHelper;
	}

	/**
	 * Format the errors and output them to the console.
	 *
	 * @param array $errors
	 * @param array $paths
	 * @param StyleInterface $style
	 * @return int Error code.
	 */
	public function formatErrors(array $errors, array $paths, StyleInterface $style): int
	{
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

		$currentDir = $this->fileHelper->normalizePath(dirname($paths[0]));
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

}
