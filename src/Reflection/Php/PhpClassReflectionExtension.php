<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
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

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\Php\PhpPropertyReflection[][] */
	private $properties = [];

	/** @var \PHPStan\Reflection\MethodReflection[][] */
	private $methodsIncludingAnnotations = [];

	/** @var \PHPStan\Reflection\Php\PhpMethodReflection[][] */
	private $nativeMethods = [];

	public function __construct(
		PhpMethodReflectionFactory $methodReflectionFactory,
		FileTypeMapper $fileTypeMapper,
		AnnotationsMethodsClassReflectionExtension $annotationsMethodsClassReflectionExtension
	)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->annotationsMethodsClassReflectionExtension = $annotationsMethodsClassReflectionExtension;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param string $propertyName
	 * @return \PHPStan\Reflection\Php\PhpPropertyReflection
	 */
	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->properties[$classReflection->getName()])) {
			$this->properties[$classReflection->getName()] = $this->createProperties($classReflection);
		}

		return $this->properties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\Php\PhpPropertyReflection[]
	 */
	private function createProperties(ClassReflection $classReflection): array
	{
		$properties = [];
		foreach ($classReflection->getNativeReflection()->getProperties() as $propertyReflection) {
			$propertyName = $propertyReflection->getName();
			$declaringClassReflection = $this->broker->getClass($propertyReflection->getDeclaringClass()->getName());
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

			$properties[$propertyName] = new PhpPropertyReflection(
				$declaringClassReflection,
				$type,
				$propertyReflection
			);
		}

		return $properties;
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
		$hierarchyDistances = $classReflection->getClassHierarchyDistances();
		foreach ($reflectionMethods as $methodReflection) {
			if ($includingAnnotations && $this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodReflection->getName())) {
				$annotationMethod = $this->annotationsMethodsClassReflectionExtension->getMethod($classReflection, $methodReflection->getName());
				if (!isset($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()])) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				if (!isset($hierarchyDistances[$methodReflection->getDeclaringClass()->getName()])) {
					throw new \PHPStan\ShouldNotHappenException();
				}

				if ($hierarchyDistances[$annotationMethod->getDeclaringClass()->getName()] < $hierarchyDistances[$methodReflection->getDeclaringClass()->getName()]) {
					$methods[$methodReflection->getName()] = $annotationMethod;
					continue;
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

			$methods[$methodReflection->getName()] = $this->methodReflectionFactory->create(
				$declaringClass,
				$methodReflection,
				$phpDocParameterTypes,
				$phpDocReturnType
			);
		}

		return $methods;
	}

}
