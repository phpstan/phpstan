<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;

class SummaryErrorFormatter implements ErrorFormatter
{

	const UNSPECIFIED = 'unspecified';

	public function formatErrors(
		AnalysisResult $analysisResult,
		\Symfony\Component\Console\Style\OutputStyle $style
	): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		$errors = [];
		$notFileSpecificErrorsCount = count($analysisResult->getNotFileSpecificErrors());
		if ($notFileSpecificErrorsCount) {
			$errors[self::UNSPECIFIED] = $notFileSpecificErrorsCount;
		}

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$description = $fileSpecificError->getRule() ?? self::UNSPECIFIED;
			$errors[$description] = ($errors[$description] ?? 0) + 1;
		}

		arsort($errors);

		$padding = strlen((string) array_values($errors)[0]);
		foreach ($errors as $description => $count) {
			$style->write(
				sprintf(
					"%s %s\n",
					str_pad((string) $count, $padding, ' ', STR_PAD_LEFT),
					$description
				)
			);
		}

		return 1;
	}

}
