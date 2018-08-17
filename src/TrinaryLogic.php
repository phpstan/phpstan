<?php declare(strict_types = 1);

namespace PHPStan;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;

/**
 * @see https://en.wikipedia.org/wiki/Three-valued_logic
 */
class TrinaryLogic
{

	private const YES = 1;
	private const MAYBE = 0;
	private const NO = -1;

	/** @var int */
	private $value;

	/** @var self[] */
	private static $registry = [];

	private function __construct(int $value)
	{
		$this->value = $value;
	}

	public static function createYes(): self
	{
		return self::create(self::YES);
	}

	public static function createNo(): self
	{
		return self::create(self::NO);
	}

	public static function createMaybe(): self
	{
		return self::create(self::MAYBE);
	}

	public static function createFromBoolean(bool $value): self
	{
		return self::create($value ? self::YES : self::NO);
	}

	private static function create(int $value): self
	{
		self::$registry[$value] = self::$registry[$value] ?? new self($value);
		return self::$registry[$value];
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

	public function toBooleanType(): BooleanType
	{
		if ($this->value === self::MAYBE) {
			return new BooleanType();
		}

		return new ConstantBooleanType($this->value === self::YES);
	}

	public function and(self ...$operands): self
	{
		$operandValues = array_column($operands, 'value');
		$operandValues[] = $this->value;
		return self::create(min($operandValues));
	}

	public function or(self ...$operands): self
	{
		$operandValues = array_column($operands, 'value');
		$operandValues[] = $this->value;
		return self::create(max($operandValues));
	}

	public static function extremeIdentity(self ...$operands): self
	{
		$operandValues = array_column($operands, 'value');
		$min = min($operandValues);
		$max = max($operandValues);
		return self::create($min === $max ? $min : self::MAYBE);
	}

	public static function maxMin(self ...$operands): self
	{
		$operandValues = array_column($operands, 'value');
		return self::create(max($operandValues) > 0 ? max($operandValues) : min($operandValues));
	}

	public function negate(): self
	{
		return self::create(-$this->value);
	}

	public function equals(self $other): bool
	{
		return $this === $other;
	}

	public function compareTo(self $other): ?self
	{
		if ($this->value > $other->value) {
			return $this;
		} elseif ($other->value > $this->value) {
			return $other;
		}

		return null;
	}

	public function describe(): string
	{
		static $labels = [
			self::NO => 'No',
			self::MAYBE => 'Maybe',
			self::YES => 'Yes',
		];

		return $labels[$this->value];
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return self::create($properties['value']);
	}

}
