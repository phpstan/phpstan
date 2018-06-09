<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool;

	public function getPropertyForRead(ClassReflection $classReflection, string $propertyName): PropertyReflection;

	public function getPropertyForWrite(ClassReflection $classReflection, string $propertyName): PropertyReflection;

}
