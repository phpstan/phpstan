<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class PhpClassReflectionExtension
	implements PropertiesClassReflectionExtension, MethodsClassReflectionExtension, BrokerAwareExtension
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
		if (!isset($this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName])) {
			$this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName] = $this->createProperty($classReflection, $propertyName, true);
		}

		return $this->propertiesIncludingAnnotations[$classReflection->getName()][$propertyName];
	}

	public function getNativeProperty(ClassReflection $classReflection, string $propertyName): PhpPropertyReflection
	{
		if (!isset($this->nativeProperties[$classReflection->getName()][$propertyName])) {
			/** @var \PHPStan\Reflection\Php\PhpPropertyReflection $property */
			$property = $this->createProperty($classReflection, $propertyName, false);
			$this->nativeProperties[$classReflection->getName()][$propertyName] = $property;
		}

		return $this->nativeProperties[$classReflection->getName()][$propertyName];
	}

	private function createProperty(
		ClassReflection $classReflection,
		string $propertyName,
		bool $includingAnnotations
	): PropertyReflection
	{
		$propertyReflection = $classReflection->getNativeReflection()->getProperty($propertyName);
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
		} elseif (!$classReflection->isAnonymous() && !$declaringClassReflection->isAnonymous() && $declaringClassReflection->getFileName() !== false) {
			$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForProperty(
				$this->broker,
				$propertyReflection->getDocComment(),
				$declaringClassReflection->getName(),
				$propertyName,
				$declaringClassReflection->getFileName()
			);

			$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
				$phpDocBlock->getFile(),
				$phpDocBlock->getClass(),
				$phpDocBlock->getDocComment()
			);
			$varTags = $resolvedPhpDoc->getVarTags();
			if (isset($varTags[0]) && count($varTags) === 1) {
				$type = $varTags[0]->getType();
			} elseif (isset($varTags[$propertyName])) {
				$type = $varTags[$propertyName]->getType();
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

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (isset($this->methodsIncludingAnnotations[$classReflection->getName()][$methodName])) {
			return $this->methodsIncludingAnnotations[$classReflection->getName()][$methodName];
		}

		$nativeMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);
		if (!isset($this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()])) {
			/** @var \PHPStan\Reflection\Php\PhpMethodReflection $method */
			$method = $this->createMethod($classReflection, $nativeMethodReflection, true);
			$this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()] = $method;
			if ($nativeMethodReflection->getName() !== $methodName) {
				$this->methodsIncludingAnnotations[$classReflection->getName()][$methodName] = $method;
			}
		}

		return $this->methodsIncludingAnnotations[$classReflection->getName()][$nativeMethodReflection->getName()];
	}

	public function getNativeMethod(ClassReflection $classReflection, string $methodName): PhpMethodReflection
	{
		if (isset($this->nativeMethods[$classReflection->getName()][$methodName])) {
			return $this->nativeMethods[$classReflection->getName()][$methodName];
		}

		$nativeMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);
		if (!isset($this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()])) {
			/** @var \PHPStan\Reflection\Php\PhpMethodReflection $method */
			$method = $this->createMethod($classReflection, $nativeMethodReflection, false);
			$this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()] = $method;
		}

		return $this->nativeMethods[$classReflection->getName()][$nativeMethodReflection->getName()];
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
		if (!$classReflection->isAnonymous() && !$declaringClass->isAnonymous() && $declaringClass->getFileName() !== false) {
			if ($methodReflection->getDocComment() !== false) {
				$phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
					$this->broker,
					$methodReflection->getDocComment(),
					$declaringClass->getName(),
					$methodReflection->getName(),
					$declaringClass->getFileName()
				);

				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
					$phpDocBlock->getFile(),
					$phpDocBlock->getClass(),
					$phpDocBlock->getDocComment()
				);
				$phpDocParameterTypes = array_map(function (ParamTag $tag): Type {
					return $tag->getType();
				}, $resolvedPhpDoc->getParamTags());
				$phpDocReturnType = $resolvedPhpDoc->getReturnTag() !== null ? $resolvedPhpDoc->getReturnTag()->getType() : null;
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
