<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantIntegerType;

class IntegerRangeType extends IntegerType
{

	/** @var int */
	private $min;

	/** @var int */
	private $max;

	public function __construct(?int $min, ?int $max)
	{
		assert($min === null || $max === null || $min <= $max);

		$this->min = $min ?? PHP_INT_MIN;
		$this->max = $max ?? PHP_INT_MAX;
	}


	public static function fromInterval(?int $min, ?int $max): Type
	{
		$min = $min ?? PHP_INT_MIN;
		$max = $max ?? PHP_INT_MAX;

		if ($min > $max) {
			return new NeverType();
		}

		if ($min === $max) {
			return new ConstantIntegerType($min);
		}

		if ($min === PHP_INT_MIN && $max === PHP_INT_MAX) {
			return new IntegerType();
		}

		return new self($min, $max);
	}


	public function getMin(): int
	{
		return $this->min;
	}


	public function getMax(): int
	{
		return $this->max;
	}


	public function describe(VerbosityLevel $level): string
	{
		if ($this->min === PHP_INT_MIN) {
			return sprintf('int<min, %d>', $this->max);
		}

		if ($this->max === PHP_INT_MAX) {
			return sprintf('int<%d, max>', $this->min);
		}

		return sprintf('int<%d, %d>', $this->min, $this->max);
	}


	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof self) {
			if ($this->min > $type->max || $this->max < $type->min) {
				return TrinaryLogic::createNo();
			}

			if ($this->min <= $type->min && $this->max >= $type->max) {
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof ConstantIntegerType) {
			if ($this->min <= $type->getValue() && $type->getValue() <= $this->max) {
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createNo();
		}

		if ($type instanceof parent) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return TrinaryLogic::createNo();
	}


	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			if ($this->min > $type->max || $this->max < $type->min) {
				return TrinaryLogic::createNo();
			}

			if ($this->min <= $type->min && $this->max >= $type->max) {
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof ConstantIntegerType) {
			if ($this->min <= $type->getValue() && $type->getValue() <= $this->max) {
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createNo();
		}

		if ($type instanceof parent) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}


	public function equals(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->min === $type->min && $this->max === $type->max;
		}

		if ($type instanceof ConstantIntegerType) {
			return $this->min === $type->getValue() && $this->max === $type->getValue();
		}

		return false;
	}


	public function generalize(): Type
	{
		return new parent();
	}


	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['min'], $properties['max']);
	}

}
