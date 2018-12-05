<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

interface OutputStyle
{
	public const VERBOSITY_QUIET = 16;
	public const VERBOSITY_NORMAL = 32;
	public const VERBOSITY_VERBOSE = 64;
	public const VERBOSITY_VERY_VERBOSE = 128;
	public const VERBOSITY_DEBUG = 256;

	public const OUTPUT_NORMAL = 1;
	public const OUTPUT_RAW = 2;
	public const OUTPUT_PLAIN = 4;

	public function write(string $message, int $options = self::OUTPUT_NORMAL): void;

	public function writeln(string $message, int $options = self::OUTPUT_NORMAL): void;

	public function setVerbosity(int $level): void;

	public function getVerbosity(): int;

	public function isQuiet(): bool;

	public function isVerbose(): bool;

	public function isVeryVerbose(): bool;

	public function isDebug(): bool;

	public function setDecorated(bool $decorated): void;

	public function isDecorated(): bool;

	public function title(string $message): void;

	public function section(string $message): void;

	/** @param $elements string[] */
	public function listing(array $elements): void;

	public function text(string $message): void;

	public function success(string $message): void;

	public function error(string $message): void;

	public function warning(string $message): void;

	public function note(string $message): void;

	public function caution(string $message): void;

	/**
	 * @param string[] $headers
	 * @param string[][] $rows
	 */
	public function table(array $headers, array $rows): void;

	/**
	 * @return mixed
	 */
	public function ask(string $question, string $default = null, callable $validator = null);

	/**
	 * @return mixed
	 */
	public function askHidden(string $question, callable $validator = null);

	public function confirm(string $question, bool $default = true): bool;

	/**
	 * @param string[] $choices
	 * @param string|int|null $default
	 * @return mixed
	 */
	public function choice(string $question, array $choices, $default = null);

	public function newLine(int $count = 1): void;

	/**
	 * Starts the progress output.
	 *
	 * @param int $max Maximum steps (0 if unknown)
	 */
	public function progressStart(int $max = 0): void;

	/**
	 * Advances the progress output X steps.
	 *
	 * @param int $step Number of steps to advance
	 */
	public function progressAdvance(int $step = 1): void;

	/**
	 * Finishes the progress output.
	 */
	public function progressFinish(): void;
}