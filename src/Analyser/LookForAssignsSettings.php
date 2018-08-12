<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;

class LookForAssignsSettings
{

	private const EARLY_TERMINATION_CONTINUE = 1;
	private const EARLY_TERMINATION_BREAK = 2;
	private const EARLY_TERMINATION_STOP = 4;
	private const EARLY_TERMINATION_ALL = self::EARLY_TERMINATION_CONTINUE
		+ self::EARLY_TERMINATION_BREAK
		+ self::EARLY_TERMINATION_STOP;
	private const EARLY_TERMINATION_CLOSURE = 8;
	private const SKIP_DEAD_BRANCHES = 16;

	/** @var int */
	private $respectEarlyTermination;

	/** @var self[] */
	private static $registry = [];

	private function __construct(
		int $respectEarlyTermination
	)
	{
		$this->respectEarlyTermination = $respectEarlyTermination;
	}

	public static function default(): self
	{
		return self::create(self::EARLY_TERMINATION_ALL);
	}

	public static function insideLoop(): self
	{
		return self::create(self::EARLY_TERMINATION_STOP + self::EARLY_TERMINATION_BREAK + self::SKIP_DEAD_BRANCHES);
	}

	public static function afterLoop(): self
	{
		return self::create(self::EARLY_TERMINATION_STOP + self::SKIP_DEAD_BRANCHES);
	}

	public static function afterSwitch(): self
	{
		return self::create(self::EARLY_TERMINATION_STOP);
	}

	public static function insideFinally(): self
	{
		return self::create(0);
	}

	public static function insideClosure(): self
	{
		return self::create(self::EARLY_TERMINATION_CLOSURE);
	}

	private static function create(int $value): self
	{
		self::$registry[$value] = self::$registry[$value] ?? new self($value);
		return self::$registry[$value];
	}

	public function skipDeadBranches(): bool
	{
		return ($this->respectEarlyTermination & self::SKIP_DEAD_BRANCHES) === self::SKIP_DEAD_BRANCHES;
	}

	public function shouldSkipBranch(\PhpParser\Node $earlyTerminationStatement): bool
	{
		return $this->isRespected($earlyTerminationStatement);
	}

	private function isRespected(\PhpParser\Node $earlyTerminationStatement): bool
	{
		if (
			$earlyTerminationStatement instanceof Break_
		) {
			return ($this->respectEarlyTermination & self::EARLY_TERMINATION_BREAK) === self::EARLY_TERMINATION_BREAK;
		}

		if (
			$earlyTerminationStatement instanceof Continue_
		) {
			return ($this->respectEarlyTermination & self::EARLY_TERMINATION_CONTINUE) === self::EARLY_TERMINATION_CONTINUE;
		}

		return ($this->respectEarlyTermination & self::EARLY_TERMINATION_STOP) === self::EARLY_TERMINATION_STOP;
	}

	public function shouldIntersectVariables(?\PhpParser\Node $earlyTerminationStatement): bool
	{
		if ($earlyTerminationStatement === null) {
			return true;
		}

		if ($this->shouldSkipBranch($earlyTerminationStatement)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $earlyTerminationStatement instanceof Break_
			|| $earlyTerminationStatement instanceof Continue_
			|| ($this->respectEarlyTermination & self::EARLY_TERMINATION_STOP) === 0;
	}

	public function shouldGeneralizeConstantTypesOfNonIdempotentOperations(): bool
	{
		return (
			($this->respectEarlyTermination & self::EARLY_TERMINATION_STOP) === self::EARLY_TERMINATION_STOP
			&& $this->respectEarlyTermination !== self::EARLY_TERMINATION_ALL
		) || $this->respectEarlyTermination === self::EARLY_TERMINATION_CLOSURE;
	}

}
