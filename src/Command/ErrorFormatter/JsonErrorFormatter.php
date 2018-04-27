<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Utils\Json;
use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class JsonErrorFormatter implements ErrorFormatter
{

	public function formatErrors(AnalysisResult $analysisResult, OutputStyle $style): int
	{
		$errorsArray = [
			'totals' => [
				'errors' => count($analysisResult->getNotFileSpecificErrors()),
				'file_errors' => count($analysisResult->getFileSpecificErrors()),
			],
			'files' => [],
			'errors' => [],
		];

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			if (!array_key_exists($fileSpecificError->getFile(), $errorsArray['files'])) {
				$errorsArray['files'][$fileSpecificError->getFile()] = [
					'errors' => 0,
					'messages' => [],
				];
			}
			$errorsArray['files'][$fileSpecificError->getFile()]['errors']++;

			$errorsArray['files'][$fileSpecificError->getFile()]['messages'][] = [
				'message' => $fileSpecificError->getMessage(),
				'line' => $fileSpecificError->getLine(),
				'ignorable' => $fileSpecificError->canBeIgnored(),
			];
		}

		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$errorsArray['errors'][] = $notFileSpecificError;
		}

		$json = Json::encode($errorsArray);

		$style->write($json);

		return $analysisResult->hasErrors() ? 1 : 0;
	}

}
