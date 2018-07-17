<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

use Symfony\Component\Console\Style\OutputStyle;

class DotsProgressPrinter implements ProgressPrinter
{

	public const NUMBER_OF_COLUMNS = 60;

	/** @var OutputStyle */
	private $style;

	/** @var int */
	private $max;

	/** @var int */
	private $progress;

	public function setOutputStyle(OutputStyle $style): void
	{
		$this->style = $style;
	}

	public function start(int $max): void
	{
		$this->max = $max;
		$this->progress = 0;
	}

	public function beforeAnalyzingFile(string $file): void
	{
	}

	public function afterAnalyzingFile(string $file, array $errors): void
	{
		if (count($errors) === 0) {
			$this->style->write('.');
		} else {
			$this->style->write('<fg=green>F</fg=green>');
		}

		++$this->progress;

		if (($this->progress % self::NUMBER_OF_COLUMNS) !== 0) {
			return;
		}

		$this->printOverview();
		$this->style->newLine();
	}

	public function finish(): void
	{
		$this->style->newLine(2);
	}

	private function printOverview(): void
	{
		$leadingSpaces = 1 + strlen((string) $this->max) - strlen((string) $this->progress);
		$percentage = round($this->progress / $this->max * 100);
		$message = sprintf(
			'%s%s / %s (%s%%)',
			str_repeat(' ', $leadingSpaces),
			$this->progress,
			$this->max,
			$percentage
		);

		$this->style->write($message);
	}

}
