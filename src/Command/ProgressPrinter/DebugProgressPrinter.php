<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

use Symfony\Component\Console\Style\OutputStyle;

class DebugProgressPrinter implements ProgressPrinter
{

	/** @var OutputStyle */
	private $style;

	public function setOutputStyle(OutputStyle $style): void
	{
		$this->style = $style;
	}

	public function start(int $max): void
	{
	}

	public function beforeAnalyzingFile(string $file): void
	{
		$this->style->writeln($file);
	}

	public function afterAnalyzingFile(string $file, array $errors): void
	{
	}

	public function finish(): void
	{
	}

}
