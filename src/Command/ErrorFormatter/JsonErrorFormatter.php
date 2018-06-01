<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Utils\Json;
use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class JsonErrorFormatter implements ErrorFormatter
{

	/** @var bool */
	private $pretty;

	public function __construct(bool $pretty)
	{
		$this->pretty = $pretty;
	}

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
			$file = $fileSpecificError->getFile();
			if (!array_key_exists($file, $errorsArray['files'])) {
				$errorsArray['files'][$file] = [
					'errors' => 0,
					'messages' => [],
				];
			}
			$errorsArray['files'][$file]['errors']++;

			$errorsArray['files'][$file]['messages'][] = [
				'message' => $fileSpecificError->getMessage(),
				'line' => $fileSpecificError->getLine(),
				'ignorable' => $fileSpecificError->canBeIgnored(),
			];
		}

		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$errorsArray['errors'][] = $notFileSpecificError;
		}

		$json = Json::encode($errorsArray, $this->pretty ? Json::PRETTY : 0);

		$style->write($json);

		return $analysisResult->hasErrors() ? 1 : 0;
	}

}
