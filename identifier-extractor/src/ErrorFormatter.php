<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use Nette\Utils\Json;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\TableErrorFormatter;
use PHPStan\Command\Output;
use PHPStan\File\RelativePathHelper;

class ErrorFormatter implements \PHPStan\Command\ErrorFormatter\ErrorFormatter
{

	public function __construct(private RelativePathHelper $relativePathHelper, private TableErrorFormatter $tableErrorFormatter)
	{
	}

	public function formatErrors(AnalysisResult $analysisResult, Output $output): int
	{
		if ($analysisResult->hasInternalErrors()) {
			return $this->tableErrorFormatter->formatErrors($analysisResult, $output);
		}

		$json = [];
		foreach ($analysisResult->getFileSpecificErrors() as $error) {
			if ($error->getIdentifier() === 'ignore.unmatchedLine') {
				continue;
			}
			if ($error->getIdentifier() !== 'phpstanIdentifierExtractor.data') {
				return $this->tableErrorFormatter->formatErrors($analysisResult, $output);
			}

			$metadata = $error->getMetadata();
			$json[] = [
				'identifiers' => $metadata['identifiers'],
				'class' => $metadata['class'],
				'file' => $this->relativePathHelper->getRelativePath($metadata['file'] ?? ''),
				'line' => $metadata['line'],
			];
		}

		$output->writeRaw(Json::encode([
			'repo' => $_SERVER['REPO'],
			'branch' => $_SERVER['BRANCH'],
			'data' => $json,
		], true));

		return 0;
	}

}
