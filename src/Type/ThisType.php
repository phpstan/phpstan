<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\Type\Generic\GenericObjectType;

class ThisType extends StaticType
{

	protected static function createStaticObjectType(string $className): ObjectType
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($className)) {
			return new ObjectType($className);
		}

		$classReflection = $broker->getClass($className);
		if ($classReflection->isGeneric()) {
			return new GenericObjectType($className, $classReflection->typeMapToList($classReflection->getTemplateTypeMap()));
		}

		return new ObjectType($className);
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('$this(%s)', $this->getStaticObjectType()->describe($level));
	}

}
