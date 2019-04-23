<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\FileTypeMapper;

class ClassReflection implements DeprecatableReflection, InternableReflection, FinalizableReflection, ReflectionWithFilename
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\PropertiesClassReflectionExtension[] */
	private $propertiesClassReflectionExtensions;

	/** @var \PHPStan\Reflection\MethodsClassReflectionExtension[] */
	private $methodsClassReflectionExtensions;

	/** @var string */
	private $displayName;

	/** @var \ReflectionClass */
	private $reflection;

	/** @var string|null */
	private $anonymousFilename;

	/** @var \PHPStan\Reflection\MethodReflection[] */
	private $methods = [];

	/** @var \PHPStan\Reflection\PropertyReflection[] */
	private $properties = [];

	/** @var \PHPStan\Reflection\ConstantReflection[] */
	private $constants;

	/** @var int[]|null */
	private $classHierarchyDistances;

	/** @var string|null */
	private $deprecatedDescription;

	/** @var bool|null */
	private $isDeprecated;

	/** @var bool|null */
	private $isInternal;

	/** @var bool|null */
	private $isFinal;

	/**
	 * @param Broker $broker
	 * @param \PHPStan\Type\FileTypeMapper $fileTypeMapper
	 * @param \PHPStan\Reflection\PropertiesClassReflectionExtension[] $propertiesClassReflectionExtensions
	 * @param \PHPStan\Reflection\MethodsClassReflectionExtension[] $methodsClassReflectionExtensions
	 * @param string $displayName
	 * @param \ReflectionClass $reflection
	 * @param string|null $anonymousFilename
	 */
	public function __construct(
		Broker $broker,
		FileTypeMapper $fileTypeMapper,
		array $propertiesClassReflectionExtensions,
		array $methodsClassReflectionExtensions,
		string $displayName,
		\ReflectionClass $reflection,
		?string $anonymousFilename
	)
	{
		$this->broker = $broker;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
		$this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
		$this->displayName = $displayName;
		$this->reflection = $reflection;
		$this->anonymousFilename = $anonymousFilename;
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
		if ($this->anonymousFilename !== null) {
			return $this->anonymousFilename;
		}
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
			foreach ($this->getNativeReflection()->getTraits() as $trait) {
				$distance++;
				if (array_key_exists($trait->getName(), $distances)) {
					continue;
				}

				$distances[$trait->getName()] = $distance;
			}

			while ($currentClassReflection->getParentClass() !== false) {
				$distance++;
				$parentClassName = $currentClassReflection->getParentClass()->getName();
				if (!array_key_exists($parentClassName, $distances)) {
					$distances[$parentClassName] = $distance;
				}
				$currentClassReflection = $currentClassReflection->getParentClass();
				foreach ($currentClassReflection->getTraits() as $trait) {
					$distance++;
					if (array_key_exists($trait->getName(), $distances)) {
						continue;
					}

					$distances[$trait->getName()] = $distance;
				}
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

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		$key = $methodName;
		if ($scope->isInClass()) {
			$key = sprintf('%s-%s', $key, $scope->getClassReflection()->getName());
		}
		if (!isset($this->methods[$key])) {
			foreach ($this->methodsClassReflectionExtensions as $extension) {
				if (!$extension->hasMethod($this, $methodName)) {
					continue;
				}

				$method = $extension->getMethod($this, $methodName);
				if ($scope->canCallMethod($method)) {
					return $this->methods[$key] = $method;
				}
				$this->methods[$key] = $method;
			}
		}

		if (!isset($this->methods[$key])) {
			$filename = $this->getFileName();
			throw new \PHPStan\Reflection\MissingMethodFromReflectionException($this->getName(), $methodName, $filename !== false ? $filename : null);
		}

		return $this->methods[$key];
	}

	public function hasNativeMethod(string $methodName): bool
	{
		return $this->getPhpExtension()->hasNativeMethod($this, $methodName);
	}

	public function getNativeMethod(string $methodName): MethodReflection
	{
		if (!$this->hasNativeMethod($methodName)) {
			$filename = $this->getFileName();
			throw new \PHPStan\Reflection\MissingMethodFromReflectionException($this->getName(), $methodName, $filename !== false ? $filename : null);
		}
		return $this->getPhpExtension()->getNativeMethod($this, $methodName);
	}

	public function hasConstructor(): bool
	{
		return $this->reflection->getConstructor() !== null;
	}

	public function getConstructor(): MethodReflection
	{
		$constructor = $this->reflection->getConstructor();
		if ($constructor === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return $this->getNativeMethod($constructor->getName());
	}

	private function getPhpExtension(): PhpClassReflectionExtension
	{
		$extension = $this->methodsClassReflectionExtensions[0];
		if (!$extension instanceof PhpClassReflectionExtension) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $extension;
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		$key = $propertyName;
		if ($scope->isInClass()) {
			$key = sprintf('%s-%s', $key, $scope->getClassReflection()->getName());
		}
		if (!isset($this->properties[$key])) {
			foreach ($this->propertiesClassReflectionExtensions as $extension) {
				if (!$extension->hasProperty($this, $propertyName)) {
					continue;
				}

				$property = $extension->getProperty($this, $propertyName);
				if ($scope->canAccessProperty($property)) {
					return $this->properties[$key] = $property;
				}
				$this->properties[$key] = $property;
			}
		}

		if (!isset($this->properties[$key])) {
			$filename = $this->getFileName();
			throw new \PHPStan\Reflection\MissingPropertyFromReflectionException($this->getName(), $propertyName, $filename !== false ? $filename : null);
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
			$filename = $this->getFileName();
			throw new \PHPStan\Reflection\MissingPropertyFromReflectionException($this->getName(), $propertyName, $filename !== false ? $filename : null);
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
		return $this->anonymousFilename !== null;
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
		return array_map(function (\ReflectionClass $interface): ClassReflection {
			return $this->broker->getClass($interface->getName());
		}, $this->getNativeReflection()->getInterfaces());
	}

	/**
	 * @return \PHPStan\Reflection\ClassReflection[]
	 */
	public function getTraits(): array
	{
		return array_map(function (\ReflectionClass $trait): ClassReflection {
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

	public function getConstant(string $name): ConstantReflection
	{
		if (!isset($this->constants[$name])) {
			$reflectionConstant = $this->getNativeReflection()->getReflectionConstant($name);
			if ($reflectionConstant === false) {
				$fileName = $this->getFileName();
				throw new \PHPStan\Reflection\MissingConstantFromReflectionException($this->getName(), $name, $fileName !== false ? $fileName : null);
			}

			$deprecatedDescription = null;
			$isDeprecated = false;
			$isInternal = false;
			if ($reflectionConstant->getDocComment() !== false && $this->getFileName() !== false) {
				$docComment = $reflectionConstant->getDocComment();
				$fileName = $this->getFileName();
				$className = $reflectionConstant->getDeclaringClass()->getName();
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, $className, null, $docComment);

				$deprecatedDescription = $resolvedPhpDoc->getDeprecatedTag() !== null ? $resolvedPhpDoc->getDeprecatedTag()->getMessage() : null;
				$isDeprecated = $resolvedPhpDoc->isDeprecated();
				$isInternal = $resolvedPhpDoc->isInternal();
			}

			$this->constants[$name] = new ClassConstantReflection(
				$this->broker->getClass($reflectionConstant->getDeclaringClass()->getName()),
				$reflectionConstant,
				$deprecatedDescription,
				$isDeprecated,
				$isInternal
			);
		}
		return $this->constants[$name];
	}

	public function hasTraitUse(string $traitName): bool
	{
		return in_array($traitName, $this->getTraitNames(), true);
	}

	/**
	 * @return string[]
	 */
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

	public function getDeprecatedDescription(): ?string
	{
		if ($this->deprecatedDescription === null && $this->isDeprecated()) {
			$resolvedPhpDoc = $this->getResolvedPhpDoc();
			if ($resolvedPhpDoc !== null && $resolvedPhpDoc->getDeprecatedTag() !== null) {
				$this->deprecatedDescription = $resolvedPhpDoc->getDeprecatedTag()->getMessage();
			}
		}

		return $this->deprecatedDescription;
	}

	public function isDeprecated(): bool
	{
		if ($this->isDeprecated === null) {
			$resolvedPhpDoc = $this->getResolvedPhpDoc();
			$this->isDeprecated = $resolvedPhpDoc !== null && $resolvedPhpDoc->isDeprecated();
		}

		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		if ($this->isInternal === null) {
			$resolvedPhpDoc = $this->getResolvedPhpDoc();
			$this->isInternal = $resolvedPhpDoc !== null && $resolvedPhpDoc->isInternal();
		}

		return $this->isInternal;
	}

	public function isFinal(): bool
	{
		if ($this->isFinal === null) {
			$resolvedPhpDoc = $this->getResolvedPhpDoc();
			$this->isFinal = $this->reflection->isFinal()
				|| ($resolvedPhpDoc !== null && $resolvedPhpDoc->isFinal());
		}

		return $this->isFinal;
	}

	private function getResolvedPhpDoc(): ?ResolvedPhpDocBlock
	{
		$fileName = $this->reflection->getFileName();
		if ($fileName === false) {
			return null;
		}

		$docComment = $this->reflection->getDocComment();
		if ($docComment === false) {
			return null;
		}

		return $this->fileTypeMapper->getResolvedPhpDoc($fileName, $this->getName(), null, $docComment);
	}

}
