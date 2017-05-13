<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ConstantIntegerType extends IntegerType implements ConstantScalarType
{
	/** @var int */
	private $value;

	public function __construct(TypeXFactory $factory, int $value)
	{
		parent::__construct($factory);
		$this->value = $value;
	}

	public function getValue(): int
	{
		return $this->value;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createIntegerType();
	}

	public function describe(): string
	{
		return 'int';
//		return (string) $this->value;
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self && $otherType->value === $this->value;
	}
}
