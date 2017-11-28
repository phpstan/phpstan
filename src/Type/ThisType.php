<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ThisType extends StaticType
{

	public function describe(): string
	{
		return sprintf('$this(%s)', $this->getBaseClass());
	}

	public function accepts(Type $type): bool
	{
		return $type instanceof self && $type->getBaseClass() === $this->getBaseClass();
	}

}
