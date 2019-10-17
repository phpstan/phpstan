<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class NeonExcludesErrorFormatter implements ErrorFormatter
{
	public function formatErrors(
		AnalysisResult $analysisResult,
		OutputStyle $style
	): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		$allMessages = [];
		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$allMessages[] = $notFileSpecificError;
		}
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$allMessages[] = $fileSpecificError->getMessage();
		}
		$allMessages = array_unique($allMessages);

		$style->writeln('parameters:');
		$style->writeln("\t" . 'ignoreErrors:');
		foreach ($allMessages as $message) {
			$style->writeln("\t\t- '''\n#" . preg_quote($message, '#') . "#\n'''");
		}

		return 1;
	}

}
