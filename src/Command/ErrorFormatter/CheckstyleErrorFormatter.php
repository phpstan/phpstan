<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Style\OutputStyle;

class CheckstyleErrorFormatter implements ErrorFormatter
{

	/** @var RelativePathHelper */
	private $relativePathHelper;

	/** @var OutputStyle */
	private $outputStyle;

	public function __construct(
		RelativePathHelper $relativePathHelper,
		OutputStyle $outputStyle
	)
	{
		$this->relativePathHelper = $relativePathHelper;
	}

	/**
	 * Formats the errors and outputs them to the console.
	 *
	 * @param \PHPStan\Command\AnalysisResult $analysisResult
	 * @return int Error code.
	 */
	public function formatErrors(
		AnalysisResult $analysisResult
	): int
	{
		$this->outputStyle->writeln('<?xml version="1.0" encoding="UTF-8"?>');
		$this->outputStyle->writeln('<checkstyle>');

		foreach ($this->groupByFile($analysisResult) as $relativeFilePath => $errors) {
			$this->outputStyle->writeln(sprintf(
				'<file name="%s">',
				$this->escape($relativeFilePath)
			));

			foreach ($errors as $error) {
				$this->outputStyle->writeln(sprintf(
					'  <error line="%d" column="1" severity="error" message="%s" />',
					$this->escape((string) $error->getLine()),
					$this->escape((string) $error->getMessage())
				));
			}
			$this->outputStyle->writeln('</file>');
		}

		$this->outputStyle->writeln('</checkstyle>');

		return $analysisResult->hasErrors() ? 1 : 0;
	}

	/**
	 * Escapes values for using in XML
	 *
	 * @param string $string
	 * @return string
	 */
	protected function escape(string $string): string
	{
		return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Group errors by file
	 *
	 * @param AnalysisResult $analysisResult
	 * @return array<string, array> Array that have as key the relative path of file
	 *                              and as value an array with occured errors.
	 */
	private function groupByFile(AnalysisResult $analysisResult): array
	{
		$files = [];

		/** @var \PHPStan\Analyser\Error $fileSpecificError */
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$relativeFilePath = $this->relativePathHelper->getRelativePath(
				$fileSpecificError->getFile()
			);

			$files[$relativeFilePath][] = $fileSpecificError;
		}

		return $files;
	}

}
