<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\SimpleXMLElementProperty;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ObjectType;

class SimpleXMLElementClassPropertyReflectionExtension implements PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getName() === 'SimpleXMLElement';
	}


	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return new SimpleXMLElementProperty($classReflection, new ObjectType('SimpleXMLElement'));
	}

}
