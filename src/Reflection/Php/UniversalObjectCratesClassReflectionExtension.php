<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;

class UniversalObjectCratesClassReflectionExtension
	implements \PHPStan\Reflection\PropertiesClassReflectionExtension
{

	/** @var string[] */
	private $classes;

	/** @var string[]|null */
	private $filteredClasses;

	/** @var \PHPStan\Reflection\ReflectionProvider */
	private $reflectionProvider;

	/**
	 * @param string[] $classes
	 */
	public function __construct(array $classes, ReflectionProvider $reflectionProvider)
	{
		$this->classes = $classes;
		$this->reflectionProvider = $reflectionProvider;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if ($this->filteredClasses === null) {
			$this->filteredClasses = array_values(array_filter($this->classes, function (string $class): bool {
				return $this->reflectionProvider->hasClass($class);
			}));
		}
		if ($classReflection->getNativeReflection()->hasProperty($propertyName)) {
			return false;
		}

		foreach ($this->filteredClasses as $className) {
			if (
				$classReflection->getName() === $className
				|| $classReflection->isSubclassOf($className)
			) {
				return true;
			}
		}

		return false;
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return new UniversalObjectCrateProperty($classReflection);
	}

}
