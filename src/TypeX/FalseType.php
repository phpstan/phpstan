<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class FalseType extends BooleanType implements ConstantScalarType
{
	public function getValue(): bool
	{
		return false;
	}

	public function describe(): string
	{
		return 'false';
	}

	public function generalize(): TypeX
	{
		return $this->factory->createBooleanType();
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self;
	}
}
