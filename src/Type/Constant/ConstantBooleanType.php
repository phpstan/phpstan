<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantBooleanType extends BooleanType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;

	/** @var bool */
	private $value;

	public function __construct(bool $value)
	{
		$this->value = $value;
	}

	public function getValue(): bool
	{
		return $this->value;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $this->value ? 'true' : 'false';
	}

	public function toBoolean(): BooleanType
	{
		return $this;
	}

	public function toNumber(): Type
	{
		return new ConstantIntegerType((int) $this->value);
	}

	public function toString(): Type
	{
		return new ConstantStringType((string) $this->value);
	}

	public function toInteger(): Type
	{
		return new ConstantIntegerType((int) $this->value);
	}

	public function toFloat(): Type
	{
		return new ConstantFloatType((float) $this->value);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
