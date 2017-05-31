<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;

class RawErrorFormatter implements ErrorFormatter
{

	public function formatErrors(
		AnalysisResult $analysisResult,
		\Symfony\Component\Console\Style\OutputStyle $style,
		int $errorsThreshold = 0
	): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$style->writeln(sprintf('?:?:%s', $notFileSpecificError));
		}

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$style->writeln(
				sprintf(
					'%s:%d:%s',
					$fileSpecificError->getFile(),
					$fileSpecificError->getLine() ?? '?',
					$fileSpecificError->getMessage()
				)
			);
		}

		return $analysisResult->getTotalErrorsCount() > $errorsThreshold ? 1 : 0;
	}

}
