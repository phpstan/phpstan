<?php declare(strict_types = 1);

namespace PHPStan;

/**
 * @see https://en.wikipedia.org/wiki/Three-valued_logic
 */
class TrinaryLogic
{

	const YES = 1;
	const MAYBE = 0;
	const NO = -1;

	/**
	 * @var int
	 */
	private $value;

	private function __construct(int $value)
	{
		$this->value = $value;
	}

	public static function createYes(): self
	{
		return new self(self::YES);
	}

	public static function createNo(): self
	{
		return new self(self::NO);
	}

	public static function createMaybe(): self
	{
		return new self(self::MAYBE);
	}

	public function yes(): bool
	{
		return $this->value === self::YES;
	}

	public function maybe(): bool
	{
		return $this->value === self::MAYBE;
	}

	public function no(): bool
	{
		return $this->value === self::NO;
	}

	public function and(self ...$operands): self
	{
		$operandValues = array_map(function (self $trinaryLogic): int {
			return $trinaryLogic->value;
		}, $operands);
		$operandValues[] = $this->value;
		return new self(min($operandValues));
	}

	public function or(self ...$operands): self
	{
		$operandValues = array_map(function (self $trinaryLogic): int {
			return $trinaryLogic->value;
		}, $operands);
		$operandValues[] = $this->value;
		return new self(max($operandValues));
	}

	public static function extremeIdentity(self ...$operands): self
	{
		$operandValues = array_map(function (self $trinaryLogic): int {
			return $trinaryLogic->value;
		}, $operands);

		$min = min($operandValues);
		$max = max($operandValues);
		return new self($min === $max ? $min : self::MAYBE);
	}

	public static function maxMin(self ...$operands): self
	{
		$operandValues = array_map(function (self $trinaryLogic): int {
			return $trinaryLogic->value;
		}, $operands);

		return new self(max($operandValues) ?: min($operandValues));
	}

	public function negate(): self
	{
		return new self(-$this->value);
	}

	public function equals(self $other): bool
	{
		return $this->value === $other->value;
	}

	public function addMaybe(): self
	{
		$value = $this->value;
		if ($value === self::NO) {
			$value = self::MAYBE;
		}

		return new self($value);
	}

	public function describe(): string
	{
		$labels = [
			self::NO => 'No',
			self::MAYBE => 'Maybe',
			self::YES => 'Yes',
		];

		return $labels[$this->value];
	}

	public static function __set_state(array $properties): self
	{
		return new self($properties['value']);
	}

}
