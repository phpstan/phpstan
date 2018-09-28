<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\PhpDoc\TypeStringResolver;
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
			'ownerElement' => 'DOMElement',
			'schemaTypeInfo' => 'bool',
			'specified' => 'bool',
			'value' => 'string',
		],
		'DOMCharacterData' => [ // extends DOMNode
			'data' => 'string',
			'length' => 'int',
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
		'DOMDocumentType' => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'name' => 'string',
			'entities' => 'DOMNamedNodeMap',
			'notations' => 'DOMNamedNodeMap',
			'internalSubset' => 'string',
		],
		'DOMElement' => [ // extends DOMNode
			'schemaTypeInfo' => 'bool',
			'tagName' => 'string',
		],
		'DOMEntity' => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'notationName' => 'string',
			'actualEncoding' => 'string',
			'encoding' => 'string',
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
			'ownerDocument' => 'DOMDocument',
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
			'publicId' => 'string',
			'systemId' => 'string',
		],
		'DOMProcessingInstruction' => [ // extends DOMNode
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

	/** @var TypeStringResolver */
	private $typeStringResolver;

	/** @var string[][] */
	private $properties = [];

	public function __construct(TypeStringResolver $typeStringResolver)
	{
		$this->typeStringResolver = $typeStringResolver;
		$this->properties = self::$defaultProperties;
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
