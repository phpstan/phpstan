<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ConstantFloatType extends FloatType implements ConstantScalarType
{
	/** @var float */
	private $value;

	public function __construct(TypeXFactory $factory, float $value)
	{
		parent::__construct($factory);
		$this->value = $value;
	}

	public function getValue(): float
	{
		return $this->value;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createFloatType();
	}

	public function describe(): string
	{
		return 'float';
//		return (string) $this->value;
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self && $otherType->value === $this->value;
	}
}
