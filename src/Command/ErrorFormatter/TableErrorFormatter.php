<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalyseCommand;
use PHPStan\Command\AnalysisResult;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Style\OutputStyle;

class TableErrorFormatter implements ErrorFormatter
{

	/** @var RelativePathHelper */
	private $relativePathHelper;

	/** @var OutputStyle */
	private $outputStyle;

	public function __construct(
		RelativePathHelper $relativePathHelper,
		OutputStyle $outputStyle
	)
	{
		$this->relativePathHelper = $relativePathHelper;
		$this->outputStyle = $outputStyle;
	}

	public function formatErrors(
		AnalysisResult $analysisResult
	): int
	{
		if (!$analysisResult->hasErrors()) {
			$this->outputStyle->success('No errors');
			if ($analysisResult->isDefaultLevelUsed()) {
				$this->outputStyle->note(sprintf(
					'PHPStan is performing only the most basic checks. You can pass a higher rule level through the --%s option (the default and current level is %d) to analyse code more thoroughly.',
					AnalyseCommand::OPTION_LEVEL,
					AnalyseCommand::DEFAULT_LEVEL
				));
			}
			return 0;
		}

		/** @var array<string, \PHPStan\Analyser\Error[]> $fileErrors */
		$fileErrors = [];
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			if (!isset($fileErrors[$fileSpecificError->getFile()])) {
				$fileErrors[$fileSpecificError->getFile()] = [];
			}

			$fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
		}

		foreach ($fileErrors as $file => $errors) {
			$rows = [];
			foreach ($errors as $error) {
				$rows[] = [
					(string) $error->getLine(),
					$error->getMessage(),
				];
			}

			$relativeFilePath = $this->relativePathHelper->getRelativePath($file);

			$this->outputStyle->table(['Line', $relativeFilePath], $rows);
		}

		if (count($analysisResult->getNotFileSpecificErrors()) > 0) {
			$this->outputStyle->table(['Error'], array_map(static function (string $error): array {
				return [$error];
			}, $analysisResult->getNotFileSpecificErrors()));
		}

		$this->outputStyle->error(sprintf($analysisResult->getTotalErrorsCount() === 1 ? 'Found %d error' : 'Found %d errors', $analysisResult->getTotalErrorsCount()));
		return 1;
	}

}
