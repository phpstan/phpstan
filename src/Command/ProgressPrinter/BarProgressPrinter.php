<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

use Symfony\Component\Console\Style\OutputStyle;

class BarProgressPrinter implements ProgressPrinter
{

	/** @var OutputStyle */
	private $style;

	public function setOutputStyle(OutputStyle $style): void
	{
		$this->style = $style;
	}

	public function start(int $max): void
	{
		$this->style->progressStart($max);
	}

	public function beforeAnalyzingFile(string $file): void
	{
	}

	public function afterAnalyzingFile(string $file, array $errors): void
	{
		$this->style->progressAdvance();
	}

	public function finish(): void
	{
		$this->style->progressFinish();
	}

}
