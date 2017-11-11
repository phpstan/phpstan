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

		/** @var \PHPStan\Analyser\Error $fileSpecificError */
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$out .= '<file name="' . $this->escape($fileSpecificError->getFile()) . '">' . "\n";
			$out .= ' ';
			$out .= '<error';
			$out .= ' line="' . $this->escape((string) $fileSpecificError->getLine()) . '"';
			$out .= ' column="1"';
			$out .= ' severity="error"';
			$out .= ' message="' . $this->escape($fileSpecificError->getMessage()) . '"';
			$out .= '/>' . "\n";
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

}
