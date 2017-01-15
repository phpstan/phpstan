<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\TypehintHelper;

class PhpDefectClassReflectionExtension implements PropertiesClassReflectionExtension
{

	private $properties = [
		'DateInterval' => [
			'y' => 'int',
			'm' => 'int',
			'd' => 'int',
			'h' => 'int',
			'i' => 'int',
			's' => 'int',
			'invert' => 'int',
			'days' => 'mixed',
		],
		'DOMDocument' => [
			'actualEncoding' => 'string',
			'config' => 'DOMConfiguration',
			'doctype' => 'DOMDocumentType',
			'documentElement' => 'DOMElement',
			'documentURI' => 'string',
			'encoding' => 'string',
			'formatOutput' => 'bool',
			'implementation' => 'DOMImplementation',
			'preserveWhiteSpace' => 'bool',
			'recover' => 'bool',
			'resolveExternals' => 'bool',
			'standalone' => 'bool',
			'strictErrorChecking' => 'bool',
			'substituteEntities' => 'bool',
			'validateOnParse' => 'bool',
			'version' => 'string',
			'xmlEncoding' => 'string',
			'xmlStandalone' => 'bool',
			'xmlVersion' => 'string',
		],
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
		$classWithProperties = $this->getClassWithProperties($classReflection);
		if ($classWithProperties === null) {
			return false;
		}
		return isset($this->properties[$classWithProperties->getName()][$propertyName]);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		$classWithProperties = $this->getClassWithProperties($classReflection);
		$typeString = $this->properties[$classWithProperties->getName()][$propertyName];
		return new PhpDefectPropertyReflection(
			$classWithProperties,
			TypehintHelper::getTypeObjectFromTypehint($typeString, false)
		);
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return \PHPStan\Reflection\ClassReflection|null
	 */
	private function getClassWithProperties(ClassReflection $classReflection)
	{
		if (isset($this->properties[$classReflection->getName()])) {
			return $classReflection;
		}

		foreach ($classReflection->getParents() as $parentClass) {
			if (isset($this->properties[$parentClass->getName()])) {
				return $parentClass;
			}
		}

		return null;
	}

}
