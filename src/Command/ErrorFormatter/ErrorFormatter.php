<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;

interface ErrorFormatter
{

	/**
	 * Formats the errors and outputs them to the console.
	 *
	 * @param \PHPStan\Command\AnalysisResult $analysisResult
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @param int $errorsThreshold of errors which can be considered as ok
	 * @return int Error code.
	 */
	public function formatErrors(
		AnalysisResult $analysisResult,
		\Symfony\Component\Console\Style\OutputStyle $style,
		int $errorsThreshold = 0
	): int;

}
