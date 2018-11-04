<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErrorsConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{

	public const OPTION_NO_PROGRESS = 'no-progress';

	/** @var bool */
	private $showProgress;

	/** @var \Symfony\Component\Console\Helper\ProgressBar */
	private $progressBar;

	public function __construct(InputInterface $input, OutputInterface $output)
	{
		parent::__construct($input, $output);
		$this->showProgress = $input->hasOption(self::OPTION_NO_PROGRESS) && !(bool) $input->getOption(self::OPTION_NO_PROGRESS);
	}

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
	 * @param int $max
	 */
	public function progressStart($max = 0): void
	{
		if (!$this->showProgress) {
			return;
		}
		parent::progressStart($max);
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param int $step
	 */
	public function progressAdvance($step = 1): void
	{
		if (!$this->showProgress) {
			return;
		}
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

	public function progressFinish(): void
	{
		if (!$this->showProgress) {
			return;
		}
		parent::progressFinish();
	}

}
