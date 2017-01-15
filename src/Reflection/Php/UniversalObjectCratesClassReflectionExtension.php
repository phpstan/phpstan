<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\ClassTypeHelperTrait;

class UniversalObjectCratesClassReflectionExtension
	implements \PHPStan\Reflection\PropertiesClassReflectionExtension
{

	use ClassTypeHelperTrait;

	/** @var string[] */
	private $classes;

	/**
	 * @param string[] $classes
	 */
	public function __construct(array $classes)
	{
		$this->classes = array_values(array_filter($classes, function (string $class): bool {
			return self::exists($class);
		}));
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if ($classReflection->getNativeReflection()->hasProperty($propertyName)) {
			return false;
		}

		foreach ($this->classes as $className) {
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
