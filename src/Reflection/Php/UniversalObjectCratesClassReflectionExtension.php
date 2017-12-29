<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;

class UniversalObjectCratesClassReflectionExtension
	implements \PHPStan\Reflection\PropertiesClassReflectionExtension, \PHPStan\Reflection\BrokerAwareExtension
{

	/** @var string[] */
	private $classes;

	/** @var string[]|null */
	private $filteredClasses;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/**
	 * @param string[] $classes
	 */
	public function __construct(array $classes)
	{
		$this->classes = $classes;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if ($this->filteredClasses === null) {
			$this->filteredClasses = array_values(array_filter($this->classes, function (string $class): bool {
				return $this->broker->hasClass($class);
			}));
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
