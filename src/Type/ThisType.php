<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateTypeHelper;

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
			$typeMap = $classReflection->getTemplateTypeMap()->map(static function (string $name, Type $type): Type {
				return TemplateTypeHelper::toArgument($type);
			});
			return new GenericObjectType(
				$className,
				$classReflection->typeMapToList($typeMap)
			);
		}

		return new ObjectType($className);
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('$this(%s)', $this->getStaticObjectType()->describe($level));
	}

}
