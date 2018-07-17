<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\ProgressPrinter\ProgressPrinter;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Style\OutputStyle;

class AnalyseApplication
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	/** @var string */
	private $memoryLimitFile;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var string */
	private $currentWorkingDirectory;

	public function __construct(
		Analyser $analyser,
		string $memoryLimitFile,
		FileHelper $fileHelper,
		string $currentWorkingDirectory
	)
	{
		$this->analyser = $analyser;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->fileHelper = $fileHelper;
		$this->currentWorkingDirectory = $currentWorkingDirectory;
	}

	/**
	 * @param string[] $files
	 * @param bool $onlyFiles
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @param \Symfony\Component\Console\Style\OutputStyle $errorStyle
	 * @param \PHPStan\Command\ErrorFormatter\ErrorFormatter $errorFormatter
	 * @param \PHPStan\Command\ProgressPrinter\ProgressPrinter $progressPrinter
	 * @param bool $defaultLevelUsed
	 * @param bool $debug
	 * @return int Error code.
	 */
	public function analyse(
		array $files,
		bool $onlyFiles,
		OutputStyle $style,
		OutputStyle $errorStyle,
		ErrorFormatter $errorFormatter,
		ProgressPrinter $progressPrinter,
		bool $defaultLevelUsed,
		bool $debug
	): int
	{
		$this->updateMemoryLimitFile();
		$errors = [];

		$progressStarted = false;
		$fileOrder = 0;
		$preFileCallback = static function (string $file) use ($errorStyle, $progressPrinter, $files, &$progressStarted): void {
			if (!$progressStarted) {
				$progressPrinter->setOutputStyle($errorStyle);
				$progressPrinter->start(count($files));
				$progressStarted = true;
			}

			$progressPrinter->beforeAnalyzingFile($file);
		};
		$postFileCallback = function (string $file, array $errors) use ($progressPrinter, &$fileOrder): void {
			$progressPrinter->afterAnalyzingFile($file, $errors);
			if ($fileOrder % 100 === 0) {
				$this->updateMemoryLimitFile();
			}
			$fileOrder++;
		};

		$errors = array_merge($errors, $this->analyser->analyse(
			$files,
			$onlyFiles,
			$preFileCallback,
			$postFileCallback,
			$debug
		));

		if ($progressStarted) {
			$progressPrinter->finish();
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
				$this->fileHelper->normalizePath($this->currentWorkingDirectory)
			),
			$style
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
