<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpParser;

use PhpParser\Node;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class PhpParserNameClassReflectionExtension implements PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->isSubclassOf(Node::class)
			&& ($classReflection->getNativeReflection()->hasProperty('name') || $classReflection->getName() === \PhpParser\Node\FunctionLike::class)
			&& $propertyName === 'namespacedName';
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return new NamespacedNameProperty($classReflection);
	}

}
