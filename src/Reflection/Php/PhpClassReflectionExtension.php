<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypehintHelper;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension */
	private $annotationsMethodsClassReflectionExtension;

	/** @var \PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension */
	private $annotationsPropertiesClassReflectionExtension;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertyReflection[][] */
	private $propertiesIncludingAnnotations = [];

	/** @var \PHPStan\Reflection\Php\PhpPropertyReflection[][] */
	private $nativeProperties;

	/** @var \PHPStan\Reflection\MethodReflection[][] */
	private $methodsIncludingAnnotations = [];

	/** @var \PHPStan\Reflection\Php\PhpMethodReflection[][] */
	private $nativeMethods = [];

	public function __construct(
		PhpMethodReflectionFactory $methodReflectionFactory,
		FileTypeMapper $fileTypeMapper,
		AnnotationsMethodsClassReflectionExtension $annotationsMethodsClassReflectionExtension,
		AnnotationsPropertiesClassReflectionExtension $annotationsPropertiesClassReflectionExtension
	)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->annotationsMethodsClassReflectionExtension = $annotationsMethodsClassReflectionExtension;
		$this->annotationsPropertiesClassReflectionExtension = $annotationsPropertiesClassReflectionExtension;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->propertiesIncludingAnnotations[$classReflection->getName()])) {
			$this->propertiesIncludingAnnotations[$classReflection->getName()] = $this->createProperties($classReflection, true);
		}

		return $this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName];
	}

	public function getNativeProperty(ClassReflection $classReflection, string $propertyName): PhpPropertyReflection
	{
		if (!isset($this->nativeProperties[$classReflection->getName()])) {
			/** @var \PHPStan\Reflection\Php\PhpPropertyReflection[] $properties */
			$properties = $this->createProperties($classReflection, false);
			$this->nativeProperties[$classReflection->getName()] = $properties;
		}

		return $this->nativeProperties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param bool $includingAnnotations
	 * @return \PHPStan\Reflection\PropertyReflection[]
	 */
	private function createProperties(
		ClassReflection $classReflection,
		bool $includingAnnotations
	): array
	{
		$properties = [];
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$properties[$propertyReflection->getName()] = $this->createProperty($classReflection, $propertyReflection, $includingAnnotations);
		}

		return $properties;
	}

	private function createProperty(
		ClassReflection $classReflection,
		\ReflectionProperty $propertyReflection,
		bool $includingAnnotations
	): PropertyReflection
	{
		$propertyName = $propertyReflection->getName();
		$declaringClassReflection = $this->broker->getClass($propertyReflection->getDeclaringClass()->getName());

		if ($includingAnnotations && $this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
			$hierarchyDistances = $classReflection->getClassHierarchyDistances();
			$annotationProperty = $this->annotationsPropertiesClassReflectionExtension->getProperty($classReflection, $propertyName);
			if (!isset($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!isset($hierarchyDistances[$propertyReflection->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()] < $hierarchyDistances[$propertyReflection->getDeclaringClass()->getName()]) {
				return $annotationProperty;
			}
		}
		if ($propertyReflection->getDocComment() === false) {
			$type = new MixedType();
		} elseif (!$declaringClassReflection->getNativeReflection()->isAnonymous() && $declaringClassReflection->getNativeReflection()->getFileName() !== false) {
			$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForProperty(
				$this->broker,
				$propertyReflection->getDocComment(),
				$declaringClassReflection->getName(),
				$propertyName,
				$declaringClassReflection->getNativeReflection()->getFileName()
			);
			$typeMap = $this->fileTypeMapper->getTypeMap($phpDocBlock->getFile());
			$typeString = $this->getPropertyAnnotationTypeString($phpDocBlock->getDocComment());
			if (isset($typeMap[$typeString])) {
				$type = $typeMap[$typeString];
			} else {
				$type = new MixedType();
			}
		} else {
			$type = new MixedType();
		}

		return new PhpPropertyReflection(
			$declaringClassReflection,
			$type,
			$propertyReflection
		);
	}

	/**
	 * @param string $phpDoc
	 * @return string|null
	 */
	private function getPropertyAnnotationTypeString(string $phpDoc)
	{
		$count = preg_match_all('#@var\s+' . FileTypeMapper::TYPE_PATTERN . '#', $phpDoc, $matches);
		if ($count !== 1) {
			return null;
		}

		return $matches[1][0];
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (!isset($this->methodsIncludingAnnotations[$classReflection->getName()])) {
			$this->methodsIncludingAnnotations[$classReflection->getName()] = $this->createMethods($classReflection, true);
		}

		$nativeMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);

		return $this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	public function getNativeMethod(ClassReflection $classReflection, string $methodName): PhpMethodReflection
	{
		if (!isset($this->nativeMethods[$classReflection->getName()])) {
			/** @var \PHPStan\Reflection\Php\PhpMethodReflection[] $methods */
			$methods = $this->createMethods($classReflection, false);
			$this->nativeMethods[$classReflection->getName()] = $methods;
		}

		$nativeMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);

		return $this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param bool $includingAnnotations
	 * @return \PHPStan\Reflection\MethodReflection[]
	 */
	private function createMethods(
		ClassReflection $classReflection,
		bool $includingAnnotations
	): array
	{
		$methods = [];
		$reflectionMethods = $classReflection->getNativeReflection()->getMethods();
		if ($classReflection->getName() === \Closure::class || $classReflection->isSubclassOf(\Closure::class)) {
			$hasInvokeMethod = false;
			foreach ($reflectionMethods as $reflectionMethod) {
				if ($reflectionMethod->getName() === '__invoke') {
					$hasInvokeMethod = true;
					break;
				}
			}
			if (!$hasInvokeMethod) {
				$reflectionMethods[] = $classReflection->getNativeReflection()->getMethod('__invoke');
			}
		}
		foreach ($reflectionMethods as $methodReflection) {
			$methods[$methodReflection->getName()] = $this->createMethod(
				$classReflection,
				$methodReflection,
				$includingAnnotations
			);
		}

		return $methods;
	}

	private function createMethod(
		ClassReflection $classReflection,
		\ReflectionMethod $methodReflection,
		bool $includingAnnotations
	): MethodReflection
	{
		if ($includingAnnotations && $this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodReflection->getName())) {
			$hierarchyDistances = $classReflection->getClassHierarchyDistances();
			$annotationMethod = $this->annotationsMethodsClassReflectionExtension->getMethod($classReflection, $methodReflection->getName());
			if (!isset($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!isset($hierarchyDistances[$methodReflection->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()] < $hierarchyDistances[$methodReflection->getDeclaringClass()->getName()]) {
				return $annotationMethod;
			}
		}
		$declaringClass = $this->broker->getClass($methodReflection->getDeclaringClass()->getName());

		$phpDocParameterTypes = [];
		$phpDocReturnType = null;
		if (!$declaringClass->getNativeReflection()->isAnonymous() && $declaringClass->getNativeReflection()->getFileName() !== false) {
			if ($methodReflection->getDocComment() !== false) {
				$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
					$this->broker,
					$methodReflection->getDocComment(),
					$declaringClass->getName(),
					$methodReflection->getName(),
					$declaringClass->getNativeReflection()->getFileName()
				);
				$typeMap = $this->fileTypeMapper->getTypeMap($phpDocBlock->getFile());
				$phpDocParameterTypes = TypehintHelper::getParameterTypesFromPhpDoc(
					$typeMap,
					array_map(function (\ReflectionParameter $parameterReflection): string {
						return $parameterReflection->getName();
					}, $methodReflection->getParameters()),
					$phpDocBlock->getDocComment()
				);
				$phpDocReturnType = TypehintHelper::getReturnTypeFromPhpDoc($typeMap, $phpDocBlock->getDocComment());
			}
		}

		return $this->methodReflectionFactory->create(
			$declaringClass,
			$methodReflection,
			$phpDocParameterTypes,
			$phpDocReturnType
		);
	}

}
