<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\FileTypeMapper;

class AnnotationsPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\PropertyReflection[][] */
	private $properties = [];

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		if (!isset($this->properties[$classReflection->getName()])) {
			$this->properties[$classReflection->getName()] = $this->createProperties($classReflection);
		}

		return isset($this->properties[$classReflection->getName()][$propertyName]);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return $this->properties[$classReflection->getName()][$propertyName];
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\PropertyReflection[]
	 */
	private function createProperties(ClassReflection $classReflection): array
	{
		$properties = [];
		foreach ($classReflection->getParentClassesNames() as $parentClassName) {
			if (!$this->broker->hasClass($parentClassName)) {
				continue;
			}
			$properties += $this->createProperties($this->broker->getClass($parentClassName));
		}

		$fileName = $classReflection->getNativeReflection()->getFileName();
		if ($fileName === false) {
			return $properties;
		}

		$docComment = $classReflection->getNativeReflection()->getDocComment();
		if ($docComment === false) {
			return $properties;
		}

		$typeMap = $this->fileTypeMapper->getTypeMap($fileName);

		preg_match_all('#@property(?:-read)?\s+' . FileTypeMapper::TYPE_PATTERN . '\s+\$([a-zA-Z0-9_]+)#', $docComment, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$typeString = $match[1];
			if (!isset($typeMap[$typeString])) {
				continue;
			}

			$properties[$match[2]] = new AnnotationPropertyReflection($classReflection, $typeMap[$typeString]);
		}

		return $properties;
	}

}
