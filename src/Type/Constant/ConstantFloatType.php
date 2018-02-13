<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;

class ConstantFloatType extends FloatType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;

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

}
