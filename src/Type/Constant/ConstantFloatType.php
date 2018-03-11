<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;

class ConstantFloatType extends FloatType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;
	use ConstantScalarToBooleanTrait;

	/** @var float */
	private $value;

	public function __construct(float $value)
	{
		$this->value = $value;
	}

	public function getValue(): float
	{
		return $this->value;
	}

	public function describe(): string
	{
		return sprintf('float(%f)', $this->value);
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
