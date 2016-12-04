<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ClassReflection
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertiesClassReflectionExtension[] */
	private $propertiesClassReflectionExtensions;

	/** @var \PHPStan\Reflection\MethodsClassReflectionExtension[] */
	private $methodsClassReflectionExtensions;

	/** @var \ReflectionClass */
	private $reflection;

	/** @var \PHPStan\Reflection\MethodReflection[] */
	private $methods = [];

	/** @var \PHPStan\Reflection\PropertyReflection[] */
	private $properties = [];

	/** @var \PHPStan\Reflection\ClassConstantReflection[] */
	private $constants;

	public function __construct(
		Broker $broker,
		array $propertiesClassReflectionExtensions,
		array $methodsClassReflectionExtensions,
		\ReflectionClass $reflection
	)
	{
		$this->broker = $broker;
		$this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
		$this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
		$this->reflection = $reflection;
	}

	public function getNativeReflection(): \ReflectionClass
	{
		return $this->reflection;
	}

	/**
	 * @return bool|\PHPStan\Reflection\ClassReflection
	 */
	public function getParentClass()
	{
		if ($this->reflection->getParentClass() === false) {
			return false;
		}

		return $this->broker->getClass($this->reflection->getParentClass()->getName());
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	public function hasProperty(string $propertyName): bool
	{
		foreach ($this->propertiesClassReflectionExtensions as $extension) {
			if ($extension->hasProperty($this, $propertyName)) {
				return true;
			}
		}

		return false;
	}

	public function hasMethod(string $methodName): bool
	{
		foreach ($this->methodsClassReflectionExtensions as $extension) {
			if ($extension->hasMethod($this, $methodName)) {
				return true;
			}
		}

		return false;
	}

	public function getMethod(string $methodName): MethodReflection
	{
		if (!isset($this->methods[$methodName])) {
			foreach ($this->methodsClassReflectionExtensions as $extension) {
				if ($extension->hasMethod($this, $methodName)) {
					return $this->methods[$methodName] = $extension->getMethod($this, $methodName);
				}
			}
		}

		if (!isset($this->methods[$methodName])) {
			throw new \PHPStan\Reflection\MissingMethodFromReflectionException($this->getName(), $methodName);
		}

		return $this->methods[$methodName];
	}

	public function getProperty(string $propertyName, Scope $scope = null): PropertyReflection
	{
		if (!isset($this->properties[$propertyName])) {
			$privateProperty = null;
			$publicProperty = null;
			foreach ($this->propertiesClassReflectionExtensions as $extension) {
				if ($extension->hasProperty($this, $propertyName)) {
					$property = $extension->getProperty($this, $propertyName);
					if ($scope !== null && $scope->canAccessProperty($property)) {
						return $this->properties[$propertyName] = $property;
					}
					$this->properties[$propertyName] = $property;
				}
			}
		}

		if (!isset($this->properties[$propertyName])) {
			throw new \PHPStan\Reflection\MissingPropertyFromReflectionException($this->getName(), $propertyName);
		}

		return $this->properties[$propertyName];
	}

	public function isAbstract(): bool
	{
		return $this->reflection->isAbstract();
	}

	public function isInterface(): bool
	{
		return $this->reflection->isInterface();
	}

	public function isTrait(): bool
	{
		return $this->reflection->isTrait();
	}

	public function isSubclassOf(string $className): bool
	{
		return $this->reflection->isSubclassOf($className);
	}

	/**
	 * @return \PHPStan\Reflection\ClassReflection[]
	 */
	public function getParents(): array
	{
		$parents = [];
		$parent = $this->getParentClass();
		while ($parent !== false) {
			$parents[] = $parent;
			$parent = $parent->getParentClass();
		}

		return $parents;
	}

	/**
	 * @return \PHPStan\Reflection\ClassReflection[]
	 */
	public function getInterfaces(): array
	{
		return array_map(function (\ReflectionClass $interface) {
			return $this->broker->getClass($interface->getName());
		}, $this->getNativeReflection()->getInterfaces());
	}

	/**
	 * @return string[]
	 */
	public function getParentClassesNames(): array
	{
		$parentNames = [];
		$currentClassReflection = $this;
		while ($currentClassReflection->getParentClass() !== false) {
			$parentNames[] = $currentClassReflection->getParentClass()->getName();
			$currentClassReflection = $currentClassReflection->getParentClass();
		}

		return $parentNames;
	}

	public function hasConstant(string $name): bool
	{
		return $this->getNativeReflection()->hasConstant($name);
	}

	public function getConstant(string $name): ClassConstantReflection
	{
		if (!isset($this->constants[$name])) {
			if (PHP_VERSION_ID < 70100) {
				$this->constants[$name] = new ObsoleteClassConstantReflection(
					$this,
					$name,
					$this->getNativeReflection()->getConstant($name)
				);
			} else {
				$reflectionConstant = $this->getNativeReflection()->getReflectionConstant($name);
				$this->constants[$name] = new ClassConstantWithVisibilityReflection(
					$this->broker->getClass($reflectionConstant->getDeclaringClass()->getName()),
					$reflectionConstant
				);
			}
		}
		return $this->constants[$name];
	}

	public function hasTraitUse(string $traitName): bool
	{
		return in_array($traitName, $this->getTraitNames(), true);
	}

	private function getTraitNames(): array
	{
		$class = $this->reflection;
		$traitNames = $class->getTraitNames();
		while ($class->getParentClass() !== false) {
			$traitNames = array_values(array_unique(array_merge($traitNames, $class->getParentClass()->getTraitNames())));
			$class = $class->getParentClass();
		}

		return $traitNames;
	}

}
