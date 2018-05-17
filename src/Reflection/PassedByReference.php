<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

class PassedByReference
{

	private const NO = 1;
	private const READS_ARGUMENT = 2;
	private const CREATES_NEW_VARIABLE = 3;

	/** @var self[] */
	private static $registry = [];

	/** @var int */
	private $value;

	private function __construct(int $value)
	{
		$this->value = $value;
	}

	private static function create(int $value): self
	{
		if (!array_key_exists($value, self::$registry)) {
			self::$registry[$value] = new self($value);
		}

		return self::$registry[$value];
	}

	public static function createNo(): self
	{
		return self::create(self::NO);
	}

	public static function createCreatesNewVariable(): self
	{
		return self::create(self::CREATES_NEW_VARIABLE);
	}

	public static function createReadsArgument(): self
	{
		return self::create(self::READS_ARGUMENT);
	}

	public function no(): bool
	{
		return $this->value === self::NO;
	}

	public function yes(): bool
	{
		return !$this->no();
	}

	public function equals(self $other): bool
	{
		return $this->value === $other->value;
	}

	public function createsNewVariable(): bool
	{
		return $this->value === self::CREATES_NEW_VARIABLE;
	}

	public function combine(self $other): self
	{
		if ($this->value > $other->value) {
			return $this;
		} elseif ($this->value < $other->value) {
			return $other;
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self($properties['value']);
	}

}
