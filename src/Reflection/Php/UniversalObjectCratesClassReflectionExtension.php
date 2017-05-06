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

	/** @var string[]|null */
	private $filteredClasses;

	/**
	 * @param string[] $classes
	 */
	public function __construct(array $classes)
	{
		$this->classes = $classes;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if ($this->filteredClasses === null) {
			$this->filteredClasses = array_values(array_filter($this->classes, function (string $class): bool {
				return self::exists($class);
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
