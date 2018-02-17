<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;

class ConstantIntegerType extends IntegerType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;

	/** @var int */
	private $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function getValue(): int
	{
		return $this->value;
	}

	public function describe(): string
	{
		return sprintf('int(%d)', $this->value);
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
