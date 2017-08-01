<?php declare(strict_types = 1);

namespace PHPStan\Type;

class StaticType extends ObjectType implements StaticResolvableType
{

	public function describe(): string
	{
		return sprintf('static(%s)', $this->getClass());
	}

	public function resolveStatic(string $className): Type
	{
		return new ObjectType($className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return new static($className);
	}

}
