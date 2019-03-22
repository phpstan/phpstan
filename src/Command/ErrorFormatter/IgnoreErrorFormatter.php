<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Neon\Neon;
use Nette\Utils\Strings;
use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class IgnoreErrorFormatter implements ErrorFormatter
{

	public function formatErrors(
		AnalysisResult $analysisResult,
		OutputStyle $style
	): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		$style->writeln('# This is an auto-generated file, do not edit.');
		$style->writeln('#');
		$style->writeln('# You can regenerate this file using this command:');
		$style->writeln('# cat /dev/null > phpstan-ignore.neon && vendor/phpstan/phpstan/bin/phpstan analyse --configuration=phpstan.neon <your configuration> --error-format=ignore > phpstan-ignore.neon');
		$style->writeln('#');
		$style->writeln('# Make sure this file is listed in the include section of your phpstan.neon.');
		$style->writeln('');
		$style->writeln('parameters:');
		$style->writeln('    ignoreErrors:');

		$errors = [];

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$file = $fileSpecificError->getFile();
			$filename = Strings::before($file, ' (in context of class ');
			$filename = $filename !== false ? $filename : $file;
			$errors[$filename][] = $fileSpecificError->getMessage();
		}

		foreach ($errors as $filename => $fileErrors) {
			$fileErrors = array_unique($fileErrors);

			foreach ($fileErrors as $message) {
				$style->writeln($this->formatError($filename, $message), OutputStyle::OUTPUT_RAW);
			}
		}

		return 1;
	}

	public function formatError(string $filename, string $message): string
	{
		return sprintf(
			"        -\n            message: %s\n            path: %s",
			Neon::encode('~' . preg_quote($message, '~') . '~'),
			Neon::encode('%currentWorkingDirectory%' . Strings::after($filename, getcwd()))
		);
	}

}
