<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class PhpDefectClassReflectionExtension implements PropertiesClassReflectionExtension
{

	/** @var array<string, array<string, string>> */
	private static $defaultProperties = [
		\DateInterval::class => [
			'y' => 'int',
			'm' => 'int',
			'd' => 'int',
			'h' => 'int',
			'i' => 'int',
			's' => 'int',
			'invert' => 'int',
			'days' => 'mixed',
			'f' => 'float',
		],
		\DatePeriod::class => [
			'recurrences' => 'int',
			'include_start_date' => 'bool',
			'start' => \DateTimeInterface::class,
			'current' => \DateTimeInterface::class,
			'end' => \DateTimeInterface::class,
			'interval' => \DateInterval::class,
		],
		'Directory' => [
			'handle' => 'resource',
			'path' => 'string',
		],
		'DOMAttr' => [ // extends DOMNode
			'name' => 'string',
			'ownerDocument' => 'DOMDocument',
			'ownerElement' => 'DOMElement',
			'schemaTypeInfo' => 'bool',
			'specified' => 'bool',
			'value' => 'string',
		],
		'DOMCharacterData' => [ // extends DOMNode
			'data' => 'string',
			'length' => 'int',
			'ownerDocument' => 'DOMDocument',
		],
		'DOMDocument' => [
			'actualEncoding' => 'string',
			'config' => 'DOMConfiguration',
			'doctype' => 'DOMDocumentType',
			'documentElement' => 'DOMElement|null',
			'documentURI' => 'string',
			'encoding' => 'string',
			'formatOutput' => 'bool',
			'implementation' => 'DOMImplementation',
			'ownerDocument' => 'null',
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
		'DOMDocumentType' => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'name' => 'string',
			'entities' => 'DOMNamedNodeMap',
			'notations' => 'DOMNamedNodeMap',
			'ownerDocument' => 'DOMDocument',
			'internalSubset' => 'string',
		],
		'DOMElement' => [ // extends DOMNode
			'ownerDocument' => 'DOMDocument',
			'schemaTypeInfo' => 'bool',
			'tagName' => 'string',
		],
		'DOMEntity' => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'notationName' => 'string',
			'actualEncoding' => 'string',
			'encoding' => 'string',
			'ownerDocument' => 'DOMDocument',
			'version' => 'string',
		],
		'DOMNamedNodeMap' => [
			'length' => 'int',
		],
		'DOMNode' => [
			'nodeName' => 'string',
			'nodeValue' => 'string',
			'nodeType' => 'int',
			'parentNode' => 'DOMNode',
			'childNodes' => 'DOMNodeList',
			'firstChild' => 'DOMNode',
			'lastChild' => 'DOMNode',
			'previousSibling' => 'DOMNode',
			'nextSibling' => 'DOMNode',
			'attributes' => 'DOMNamedNodeMap',
			'ownerDocument' => 'DOMDocument|null',
			'namespaceURI' => 'string',
			'prefix' => 'string',
			'localName' => 'string',
			'baseURI' => 'string',
			'textContent' => 'string',
		],
		'DOMNodeList' => [
			'length' => 'int',
		],
		'DOMNotation' => [ // extends DOMNode
			'ownerDocument' => 'DOMDocument',
			'publicId' => 'string',
			'systemId' => 'string',
		],
		'DOMProcessingInstruction' => [ // extends DOMNode
			'ownerDocument' => 'DOMDocument',
			'target' => 'string',
			'data' => 'string',
		],
		'DOMText' => [ // extends DOMCharacterData
			'wholeText' => 'string',
		],
		'DOMXPath' => [ // extends DOMCharacterData
			'document' => 'DOMDocument',
		],
		'Ds\\Pair' => [
			'key' => 'mixed',
			'value' => 'mixed',
		],
		'XMLReader' => [
			'attributeCount' => 'int',
			'baseURI' => 'string',
			'depth' => 'int',
			'hasAttributes' => 'bool',
			'hasValue' => 'bool',
			'isDefault' => 'bool',
			'isEmptyElement' => 'bool',
			'localName' => 'string',
			'name' => 'string',
			'namespaceURI' => 'string',
			'nodeType' => 'int',
			'prefix' => 'string',
			'value' => 'string',
			'xmlLang' => 'string',
		],
		'ZipArchive' => [
			'status' => 'int',
			'statusSys' => 'int',
			'numFiles' => 'int',
			'filename' => 'string',
			'comment' => 'string',
		],
		'LibXMLError' => [
			'level' => 'int',
			'code' => 'int',
			'column' => 'int',
			'message' => 'string',
			'file' => 'string',
			'line' => 'int',
		],
	];

	/** @var \PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension */
	private $annotationsPropertiesClassReflectionExtension;

	/** @var TypeStringResolver */
	private $typeStringResolver;

	/** @var string[][] */
	private $properties = [];

	public function __construct(
		TypeStringResolver $typeStringResolver,
		AnnotationsPropertiesClassReflectionExtension $annotationsPropertiesClassReflectionExtension
	)
	{
		$this->typeStringResolver = $typeStringResolver;
		$this->properties = self::$defaultProperties;
		$this->annotationsPropertiesClassReflectionExtension = $annotationsPropertiesClassReflectionExtension;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		$classWithProperties = $this->getClassWithProperties($classReflection, $propertyName);
		return $classWithProperties !== null;
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		/** @var \PHPStan\Reflection\ClassReflection $classWithProperties */
		$classWithProperties = $this->getClassWithProperties($classReflection, $propertyName);

		if ($this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
			$hierarchyDistances = $classReflection->getClassHierarchyDistances();
			$annotationProperty = $this->annotationsPropertiesClassReflectionExtension->getProperty($classReflection, $propertyName);
			if (!isset($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			if (!isset($hierarchyDistances[$classWithProperties->getName()])) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($hierarchyDistances[$annotationProperty->getDeclaringClass()->getName()] < $hierarchyDistances[$classWithProperties->getName()]) {
				return $annotationProperty;
			}
		}

		$typeString = $this->properties[$classWithProperties->getName()][$propertyName];
		return new PhpDefectPropertyReflection(
			$classWithProperties,
			$this->typeStringResolver->resolve($typeString)
		);
	}

	private function getClassWithProperties(ClassReflection $classReflection, string $propertyName): ?\PHPStan\Reflection\ClassReflection
	{
		if (isset($this->properties[$classReflection->getName()][$propertyName])) {
			return $classReflection;
		}

		foreach ($classReflection->getParents() as $parentClass) {
			if (isset($this->properties[$parentClass->getName()][$propertyName])) {
				return $parentClass;
			}
		}

		return null;
	}

}
