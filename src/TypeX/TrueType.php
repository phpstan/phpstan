<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class TrueType extends BooleanType implements ConstantScalarType
{
	public function getValue(): bool
	{
		return true;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createBooleanType();
	}

	public function describe(): string
	{
		return 'true';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self;
	}
}
