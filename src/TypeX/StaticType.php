<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Broker\Broker;
use PHPStan\Type\StaticResolvableType;


class StaticType extends ObjectType implements StaticResolvableType
{

	public function __construct(TypeXFactory $factory, Broker $broker, string $className)
	{
		parent::__construct($factory, $broker, $className);
	}

	public function describe(): string
	{
		return sprintf('static(%s)', $this->className);
	}

	public function resolveStatic(string $className): \PHPStan\Type\Type
	{
		return $this->factory->createObjectType($className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createStaticType($className);
	}

}
