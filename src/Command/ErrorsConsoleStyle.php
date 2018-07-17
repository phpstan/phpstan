<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Helper\ProgressBar;

class ErrorsConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{

	/** @var \Symfony\Component\Console\Helper\ProgressBar */
	private $progressBar;

	/**
	 * @param string[] $headers
	 * @param string[][] $rows
	 */
	public function table(array $headers, array $rows): void
	{
		/** @var int $terminalWidth */
		$terminalWidth = (new \Symfony\Component\Console\Terminal())->getWidth();
		$maxHeaderWidth = strlen($headers[0]);
		foreach ($rows as $row) {
			$length = strlen($row[0]);
			if ($maxHeaderWidth !== 0 && $length <= $maxHeaderWidth) {
				continue;
			}

			$maxHeaderWidth = $length;
		}

		$wrap = static function ($rows) use ($terminalWidth, $maxHeaderWidth) {
			return array_map(static function ($row) use ($terminalWidth, $maxHeaderWidth) {
				return array_map(static function ($s) use ($terminalWidth, $maxHeaderWidth) {
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
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param int $max
	 */
	public function createProgressBar($max = 0): ProgressBar
	{
		$this->progressBar = parent::createProgressBar($max);
		$this->progressBar->setOverwrite(true);
		return $this->progressBar;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param int $step
	 */
	public function progressAdvance($step = 1): void
	{
		if ($step > 0) {
			$stepTime = (time() - $this->progressBar->getStartTime()) / $step;
			if ($stepTime > 0 && $stepTime < 1) {
				$this->progressBar->setRedrawFrequency((int) (1 / $stepTime));
			} else {
				$this->progressBar->setRedrawFrequency(1);
			}
		}

		$this->progressBar->setProgress($this->progressBar->getProgress() + $step);
	}

}
