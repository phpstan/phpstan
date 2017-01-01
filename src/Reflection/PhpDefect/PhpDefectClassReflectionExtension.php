<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\TypehintHelper;

class PhpDefectClassReflectionExtension implements PropertiesClassReflectionExtension
{

	private $properties = [
		'ZipArchive' => [
			'status' => 'int',
			'statusSys' => 'int',
			'numFiles' => 'int',
			'filename' => 'string',
			'comment' => 'string',
		],
	];

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return isset($this->properties[$classReflection->getName()][$propertyName]);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		$typeString = $this->properties[$classReflection->getName()][$propertyName];
		return new PhpDefectPropertyReflection(
			$classReflection,
			TypehintHelper::getTypeObjectFromTypehint($typeString, false)
		);
	}

}
