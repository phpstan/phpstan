<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErrorsConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $output;

	/** @var \Symfony\Component\Console\Helper\ProgressBar|null */
	private $progressBar;

	public function __construct(InputInterface $input, OutputInterface $output)
	{
		parent::__construct($input, $output);
		$this->output = $output;
	}

	public function table(array $headers, array $rows)
	{
		$application = new Application();
		$dimensions = $application->getTerminalDimensions();
		$terminalWidth = $dimensions[0] ?: self::MAX_LINE_LENGTH;
		$maxHeaderWidth = strlen($headers[0]);
		foreach ($rows as $row) {
			$length = strlen($row[0]);
			if ($maxHeaderWidth === null || $length > $maxHeaderWidth) {
				$maxHeaderWidth = $length;
			}
		}

		$wrap = function ($rows) use ($terminalWidth, $maxHeaderWidth) {
			return array_map(function ($row) use ($terminalWidth, $maxHeaderWidth) {
				return array_map(function ($s) use ($terminalWidth, $maxHeaderWidth) {
					if ($terminalWidth > $maxHeaderWidth + 5) {
						return wordwrap(
							$s,
							$terminalWidth - $maxHeaderWidth - 5,
							"\n",
							true
						);
					}

					return $s;
				}, $row);
			}, $rows);
		};

		parent::table($headers, $wrap($rows));
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.Typehints.TypeHintDeclaration.missingParameterTypeHint
	 * @param int $max
	 */
	public function createProgressBar($max = 0): ProgressBar
	{
		$this->progressBar = parent::createProgressBar($max);
		return $this->progressBar;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.Typehints.TypeHintDeclaration.missingParameterTypeHint
	 * @param int $step
	 */
	public function progressAdvance($step = 1)
	{
		if ($this->output->isDecorated() && $step > 0) {
			$stepTime = (time() - $this->progressBar->getStartTime()) / $step;
			if ($stepTime > 0 && $stepTime < 1) {
				$this->progressBar->setRedrawFrequency(1 / $stepTime);
			} else {
				$this->progressBar->setRedrawFrequency(1);
			}
		}

		$this->progressBar->setProgress($this->progressBar->getProgress() + $step);
	}

}
