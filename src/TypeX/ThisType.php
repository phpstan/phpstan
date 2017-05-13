<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\StaticResolvableType;


class ThisType extends StaticType
{

	public function describe(): string
	{
		return sprintf('$this(%s)', $this->className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createThisType($className);
	}

}
