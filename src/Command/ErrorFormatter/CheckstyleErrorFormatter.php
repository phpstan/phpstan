<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Style\OutputStyle;

class CheckstyleErrorFormatter implements ErrorFormatter
{

	/** @var RelativePathHelper */
	private $relativePathHelper;

	public function __construct(RelativePathHelper $relativePathHelper)
	{
		$this->relativePathHelper = $relativePathHelper;
	}

	/**
	 * Formats the errors and outputs them to the console.
	 *
	 * @param \PHPStan\Command\AnalysisResult $analysisResult
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @return int Error code.
	 */
	public function formatErrors(
		AnalysisResult $analysisResult,
		OutputStyle $style
	): int
	{
		$style->writeln('<?xml version="1.0" encoding="UTF-8"?>');
		$style->writeln('<checkstyle>');

		foreach ($this->groupByFile($analysisResult) as $relativeFilePath => $errors) {
			$style->writeln(sprintf(
				'<file name="%s">',
				$this->escape($relativeFilePath)
			));

			foreach ($errors as $error) {
				$style->writeln(sprintf(
					'  <error line="%d" column="1" severity="error" message="%s" />',
					$this->escape((string) $error->getLine()),
					$this->escape((string) $error->getMessage())
				));
			}
			$style->writeln('</file>');
		}

		$style->writeln('</checkstyle>');

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
