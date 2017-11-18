<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;

class LookForAssignsSettings
{

	const EARLY_TERMINATION_CONTINUE = 1;
	const EARLY_TERMINATION_BREAK = 2;
	const EARLY_TERMINATION_STOP = 4;
	const EARLY_TERMINATION_ALL = self::EARLY_TERMINATION_CONTINUE
		+ self::EARLY_TERMINATION_BREAK
		+ self::EARLY_TERMINATION_STOP;

	/**
	 * @var bool
	 */
	private $isSwitch;

	/**
	 * @var int
	 */
	private $respectEarlyTermination;

	private function __construct(
		bool $isSwitch,
		int $respectEarlyTermination
	)
	{
		$this->isSwitch = $isSwitch;
		$this->respectEarlyTermination = $respectEarlyTermination;
	}

	public static function default(): self
	{
		return new self(
			false,
			self::EARLY_TERMINATION_ALL
		);
	}

	public static function insideSwitch(): self
	{
		return new self(
			true,
			self::EARLY_TERMINATION_STOP
		);
	}

	public static function insideLoop(): self
	{
		return new self(
			false,
			self::EARLY_TERMINATION_STOP + self::EARLY_TERMINATION_BREAK
		);
	}

	public static function insideFinally(): self
	{
		return new self(
			false,
			0
		);
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

	public function shouldIntersectVariables(\PhpParser\Node $earlyTerminationStatement = null): bool
	{
		if ($earlyTerminationStatement === null) {
			return true;
		}

		return $earlyTerminationStatement instanceof Break_
			|| $earlyTerminationStatement instanceof Continue_
			|| ($this->respectEarlyTermination & self::EARLY_TERMINATION_STOP) === 0;
	}

	public function isSwitch(): bool
	{
		return $this->isSwitch;
	}

}
