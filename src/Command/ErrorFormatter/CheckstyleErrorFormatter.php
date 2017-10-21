<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class CheckstyleErrorFormatter implements ErrorFormatter
{

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
		$returnCode = 1;
		if (!$analysisResult->hasErrors()) {
			$returnCode = 0;
		}

		$out = '';

		foreach ($this->groupByFile($analysisResult) as $relativeFilePath => $errors) {
			$out .= '<file name="' . $this->escape($relativeFilePath) . '">' . "\n";
			foreach ($errors as $error) {
				$out .= sprintf(
					'  <error line="%d" column="1" severity="error" message="%s" />' . "\n",
					$this->escape((string) $error->getLine()),
					$this->escape((string) $error->getMessage())
				);
			}
			$out .= '</file>' . "\n";
		}

		$style->write('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$style->write('<checkstyle>' . "\n");
		if ($out !== '') {
			$style->write($out);
		}
		$style->write('</checkstyle>' . "\n");

		return $returnCode;
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
			$relativeFilePath = RelativePathHelper::getRelativePath(
				$analysisResult->getCurrentDirectory(),
				$fileSpecificError->getFile()
			);

			$files[$relativeFilePath][] = $fileSpecificError;
		}

		return $files;
	}

}
