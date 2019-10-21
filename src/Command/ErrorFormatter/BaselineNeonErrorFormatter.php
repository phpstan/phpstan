<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Neon\Neon;
use PHPStan\Command\AnalysisResult;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use function preg_quote;

class BaselineNeonErrorFormatter implements ErrorFormatter
{

	/** @var \PHPStan\File\RelativePathHelper */
	private $relativePathHelper;

	public function __construct(RelativePathHelper $relativePathHelper)
	{
		$this->relativePathHelper = $relativePathHelper;
	}

	public function formatErrors(
		AnalysisResult $analysisResult,
		OutputStyle $style
	): int
	{
		if (!$analysisResult->hasErrors()) {
			$style->writeln(Neon::encode([
				'parameters' => [
					'ignoreErrors' => [],
				],
			], Neon::BLOCK), OutputInterface::OUTPUT_RAW);
			return 0;
		}

		$fileErrors = [];
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			if (!$fileSpecificError->canBeIgnored()) {
				continue;
			}
			$fileErrors[$fileSpecificError->getFilePath()][] = $fileSpecificError->getMessage();
		}

		$errorsToOutput = [];
		foreach ($fileErrors as $file => $errorMessages) {
			$fileErrorsCounts = [];
			foreach ($errorMessages as $errorMessage) {
				if (!isset($fileErrorsCounts[$errorMessage])) {
					$fileErrorsCounts[$errorMessage] = 1;
					continue;
				}

				$fileErrorsCounts[$errorMessage]++;
			}

			foreach ($fileErrorsCounts as $message => $count) {
				$errorsToOutput[] = [
					'message' => '#^' . preg_quote($message, '#') . '$#',
					'count' => $count,
					'path' => $this->relativePathHelper->getRelativePath($file),
				];
			}
		}

		$style->writeln(Neon::encode([
			'parameters' => [
				'ignoreErrors' => $errorsToOutput,
			],
		], Neon::BLOCK), OutputInterface::OUTPUT_RAW);

		return 1;
	}

}
