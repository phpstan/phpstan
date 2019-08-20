<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Utils\Json;
use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @see https://docs.gitlab.com/ee/user/project/merge_requests/code_quality.html#implementing-a-custom-tool
 */
class GitlabErrorFormatter implements ErrorFormatter
{

	public function formatErrors(AnalysisResult $analysisResult, OutputStyle $style): int
	{
		$errorsArray = [];

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$error = [
				'description' => $fileSpecificError->getMessage(),
				'fingerprint' => hash(
					'sha256',
					implode(
						[
							$fileSpecificError->getFile(),
							$fileSpecificError->getLine(),
							$fileSpecificError->getMessage(),
						]
					)
				),
				'location' => [
					'path' => $fileSpecificError->getFile(),
					'lines' => [
						'begin' => $fileSpecificError->getLine(),
					],
				],
			];

			if (!$fileSpecificError->canBeIgnored()) {
				$error['severity'] = 'blocker';
			}

			$errorsArray[] = $error;
		}

		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$errorsArray[] = [
				'description' => $notFileSpecificError,
				'fingerprint' => hash('sha256', $notFileSpecificError),
				'location' => [
					'path' => '',
					'lines' => [
						'begin' => 0,
					],
				],
			];
		}

		$json = Json::encode($errorsArray, Json::PRETTY);

		$style->write($json);

		return $analysisResult->hasErrors() ? 1 : 0;
	}

}
