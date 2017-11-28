<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;

class ClassReflection
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertiesClassReflectionExtension[] */
	private $propertiesClassReflectionExtensions;

	/** @var \PHPStan\Reflection\MethodsClassReflectionExtension[] */
	private $methodsClassReflectionExtensions;

	/** @var string */
	private $displayName;

	/** @var \ReflectionClass */
	private $reflection;

	/** @var bool */
	private $anonymous;

	/** @var \PHPStan\Reflection\MethodReflection[] */
	private $methods = [];

	/** @var \PHPStan\Reflection\PropertyReflection[] */
	private $properties = [];

	/** @var \PHPStan\Reflection\ClassConstantReflection[] */
	private $constants;

	/** @var int[]|null */
	private $classHierarchyDistances;

	public function __construct(
		Broker $broker,
		array $propertiesClassReflectionExtensions,
		array $methodsClassReflectionExtensions,
		string $displayName,
		\ReflectionClass $reflection,
		bool $anonymous
	)
	{
		$this->broker = $broker;
		$this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
		$this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
		$this->displayName = $displayName;
		$this->reflection = $reflection;
		$this->anonymous = $anonymous;
	}

	public function getNativeReflection(): \ReflectionClass
	{
		return $this->reflection;
	}

	/**
	 * @return string|false
	 */
	public function getFileName()
	{
		$fileName = $this->reflection->getFileName();
		if ($fileName === false) {
			return false;
		}

		if (!file_exists($fileName)) {
			return false;
		}

		return $fileName;
	}

	/**
	 * @return false|\PHPStan\Reflection\ClassReflection
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

	public function getDisplayName(): string
	{
		return $this->displayName;
	}

	/**
	 * @return int[]
	 */
	public function getClassHierarchyDistances(): array
	{
		if ($this->classHierarchyDistances === null) {
			$distance = 0;
			$distances = [
				$this->getName() => $distance,
			];
			$currentClassReflection = $this->getNativeReflection();
			while ($currentClassReflection->getParentClass() !== false) {
				$distance++;
				$parentClassName = $currentClassReflection->getParentClass()->getName();
				if (!array_key_exists($parentClassName, $distances)) {
					$distances[$parentClassName] = $distance;
				}
				$currentClassReflection = $currentClassReflection->getParentClass();
			}
			foreach ($this->getNativeReflection()->getInterfaces() as $interface) {
				$distance++;
				if (array_key_exists($interface->getName(), $distances)) {
					continue;
				}

				$distances[$interface->getName()] = $distance;
			}

			$this->classHierarchyDistances = $distances;
		}

		return $this->classHierarchyDistances;
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

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		$key = $methodName;
		if ($scope->isInClass()) {
			$key = sprintf('%s-%s', $key, $scope->getClassReflection()->getName());
		}
		if (!isset($this->methods[$key])) {
			foreach ($this->methodsClassReflectionExtensions as $extension) {
				if ($extension->hasMethod($this, $methodName)) {
					$method = $extension->getMethod($this, $methodName);
					if ($scope->canCallMethod($method)) {
						return $this->methods[$key] = $method;
					}
					$this->methods[$key] = $method;
				}
			}
		}

		if (!isset($this->methods[$key])) {
			throw new \PHPStan\Reflection\MissingMethodFromReflectionException($this->getName(), $methodName);
		}

		return $this->methods[$key];
	}

	public function hasNativeMethod(string $methodName): bool
	{
		return $this->getPhpExtension()->hasMethod($this, $methodName);
	}

	public function getNativeMethod(string $methodName): PhpMethodReflection
	{
		if (!$this->hasNativeMethod($methodName)) {
			throw new \PHPStan\Reflection\MissingMethodFromReflectionException($this->getName(), $methodName);
		}
		return $this->getPhpExtension()->getNativeMethod($this, $methodName);
	}

	private function getPhpExtension(): PhpClassReflectionExtension
	{
		$extension = $this->methodsClassReflectionExtensions[0];
		if (!$extension instanceof PhpClassReflectionExtension) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $extension;
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		$key = $propertyName;
		if ($scope->isInClass()) {
			$key = sprintf('%s-%s', $key, $scope->getClassReflection()->getName());
		}
		if (!isset($this->properties[$key])) {
			foreach ($this->propertiesClassReflectionExtensions as $extension) {
				if ($extension->hasProperty($this, $propertyName)) {
					$property = $extension->getProperty($this, $propertyName);
					if ($scope->canAccessProperty($property)) {
						return $this->properties[$key] = $property;
					}
					$this->properties[$key] = $property;
				}
			}
		}

		if (!isset($this->properties[$key])) {
			throw new \PHPStan\Reflection\MissingPropertyFromReflectionException($this->getName(), $propertyName);
		}

		return $this->properties[$key];
	}

	public function hasNativeProperty(string $propertyName): bool
	{
		return $this->getPhpExtension()->hasProperty($this, $propertyName);
	}

	public function getNativeProperty(string $propertyName): PhpPropertyReflection
	{
		if (!$this->hasNativeProperty($propertyName)) {
			throw new \PHPStan\Reflection\MissingPropertyFromReflectionException($this->getName(), $propertyName);
		}
		return $this->getPhpExtension()->getNativeProperty($this, $propertyName);
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

	public function isAnonymous(): bool
	{
		return $this->anonymous;
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
	 * @return \PHPStan\Reflection\ClassReflection[]
	 */
	public function getTraits(): array
	{
		return array_map(function (\ReflectionClass $trait) {
			return $this->broker->getClass($trait->getName());
		}, $this->getNativeReflection()->getTraits());
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
