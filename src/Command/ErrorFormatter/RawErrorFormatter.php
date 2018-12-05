<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Style\OutputStyle;

class RawErrorFormatter implements ErrorFormatter
{

	/** @var OutputStyle */
	private $outputStyle;

	/**
	 * RawErrorFormatter constructor.
	 * @param OutputStyle $outputStyle
	 */
	public function __construct(OutputStyle $outputStyle)
	{
		$this->outputStyle = $outputStyle;
	}


	public function formatErrors(
		AnalysisResult $analysisResult
	): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
			$this->outputStyle->writeln(sprintf('?:?:%s', $notFileSpecificError));
		}

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$this->outputStyle->writeln(
				sprintf(
					'%s:%d:%s',
					$fileSpecificError->getFile(),
					$fileSpecificError->getLine() ?? '?',
					$fileSpecificError->getMessage()
				)
			);
		}

		return 1;
	}

}
