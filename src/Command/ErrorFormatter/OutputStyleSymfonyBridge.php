<?php declare(strict_types = 1);


namespace PHPStan\Command\ErrorFormatter;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;

final class OutputStyleSymfonyBridge implements OutputStyle
{

	/** @var \Symfony\Component\Console\Style\OutputStyle */
	private $style;

	public function __construct(\Symfony\Component\Console\Style\OutputStyle $style)
	{
		$this->style = $style;
	}

	public function write(string $message, int $options = self::OUTPUT_NORMAL): void
	{
		$this->style->write($message, false, $options);
	}

	public function writeln(string $message, int $options = self::OUTPUT_NORMAL): void
	{
		$this->style->writeln($message, $options);
	}

	public function setVerbosity(int $level): void
	{
		$this->style->setVerbosity($level);
	}

	public function getVerbosity(): int
	{
		return $this->style->getVerbosity();
	}

	public function isQuiet(): bool
	{
		return $this->style->isQuiet();
	}

	public function isVerbose(): bool
	{
		return $this->style->isVerbose();
	}

	public function isVeryVerbose(): bool
	{
		return $this->style->isVeryVerbose();
	}

	public function isDebug(): bool
	{
		return $this->style->isDebug();
	}

	public function setDecorated(bool $decorated): void
	{
		$this->style->setDecorated($decorated);
	}

	public function isDecorated(): bool
	{
		return $this->style->isDecorated();
	}


	public function title(string $message): void
	{
		$this->style->title($message);
	}

	public function section(string $message): void
	{
		$this->style->section($message);
	}

	/** @param $elements string[] */
	public function listing(array $elements): void
	{
		$this->style->listing($elements);
	}

	public function text(string $message): void
	{
		$this->style->text($message);
	}

	public function success(string $message): void
	{
		$this->style->success($message);
	}

	public function error(string $message): void
	{
		$this->style->error($message);
	}

	public function warning(string $message): void
	{
		$this->style->warning($message);
	}

	public function note(string $message): void
	{
		$this->style->note($message);
	}

	public function caution(string $message): void
	{
		$this->style->caution($message);
	}

	/**
	 * @param string[] $headers
	 * @param string[][] $rows
	 */
	public function table(array $headers, array $rows): void
	{
		$this->style->table($headers, $rows);
	}

	/**
	 * @return mixed
	 */
	public function ask(string $question, string $default = null, callable $validator = null)
	{
		return $this->style->ask($question, $default, $validator);
	}

	/**
	 * @return mixed
	 */
	public function askHidden(string $question, callable $validator = null)
	{
		return $this->style->askHidden($question, $validator);
	}

	public function confirm(string $question, bool $default = true): bool
	{
		return $this->style->confirm($question, $default);
	}

	/**
	 * @param string[] $choices
	 * @param string|int|null $default
	 * @return mixed
	 */
	public function choice(string $question, array $choices, $default = null)
	{
		return $this->style->choice($question, $choices, $default);
	}

	public function newLine(int $count = 1): void
	{
		$this->style->newLine($count);
	}

	/**
	 * Starts the progress output.
	 *
	 * @param int $max Maximum steps (0 if unknown)
	 */
	public function progressStart(int $max = 0): void
	{
		$this->style->progressStart($max);
	}

	/**
	 * Advances the progress output X steps.
	 *
	 * @param int $step Number of steps to advance
	 */
	public function progressAdvance(int $step = 1): void
	{
		$this->style->progressAdvance($step);
	}

	/**
	 * Finishes the progress output.
	 */
	public function progressFinish(): void
	{
		$this->style->progressFinish();
	}
}