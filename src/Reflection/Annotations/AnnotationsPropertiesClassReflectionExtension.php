<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;

class AnnotationsPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Reflection\PropertyReflection[][] */
	private $properties = [];

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if (!isset($this->properties[$classReflection->getName()])) {
			$this->properties[$classReflection->getName()] = $this->createProperties($classReflection, $classReflection);
		}

		return isset($this->properties[$classReflection->getName()][$propertyName]);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return $this->properties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @return \PHPStan\Reflection\PropertyReflection[]
	 */
	private function createProperties(
		ClassReflection $classReflection,
		ClassReflection $declaringClass
	): array
	{
		$properties = [];
		foreach ($classReflection->getTraits() as $traitClass) {
			$properties += $this->createProperties($traitClass, $classReflection);
		}
		foreach ($classReflection->getParents() as $parentClass) {
			$properties += $this->createProperties($parentClass, $parentClass);
			foreach ($parentClass->getTraits() as $traitClass) {
				$properties += $this->createProperties($traitClass, $parentClass);
			}
		}

		foreach ($classReflection->getInterfaces() as $interfaceClass) {
			$properties += $this->createProperties($interfaceClass, $interfaceClass);
		}

		$fileName = $classReflection->getFileName();
		if ($fileName === false) {
			return $properties;
		}

		$docComment = $classReflection->getNativeReflection()->getDocComment();
		if ($docComment === false) {
			return $properties;
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, $classReflection->getName(), null, $docComment);
		foreach ($resolvedPhpDoc->getPropertyTags() as $propertyName => $propertyTag) {
			$properties[$propertyName] = new AnnotationPropertyReflection(
				$declaringClass,
				$propertyTag->getType(),
				$propertyTag->isReadable(),
				$propertyTag->isWritable()
			);
		}

		return $properties;
	}

}
