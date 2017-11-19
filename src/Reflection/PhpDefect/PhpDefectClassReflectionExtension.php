<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

class PhpDefectClassReflectionExtension implements PropertiesClassReflectionExtension
{

	/** @var string[][] */
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
		],
		\DOMAttr::class => [ // extends DOMNode
			'name' => 'string',
			'ownerElement' => \DOMElement::class,
			'schemaTypeInfo' => 'bool',
			'specified' => 'bool',
			'value' => 'string',
		],
		\DOMCharacterData::class => [ // extends DOMNode
			'data' => 'string',
			'length' => 'int',
		],
		\DOMDocument::class => [
			'actualEncoding' => 'string',
			'config' => \DOMConfiguration::class,
			'doctype' => \DOMDocumentType::class,
			'documentElement' => \DOMElement::class,
			'documentURI' => 'string',
			'encoding' => 'string',
			'formatOutput' => 'bool',
			'implementation' => \DOMImplementation::class,
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
		\DOMDocumentType::class => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'name' => 'string',
			'entities' => \DOMNamedNodeMap::class,
			'notations' => \DOMNamedNodeMap::class,
			'internalSubset' => 'string',
		],
		\DOMElement::class => [ // extends DOMNode
			'schemaTypeInfo' => 'bool',
			'tagName' => 'string',
		],
		\DOMEntity::class => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
			'notationName' => 'string',
			'actualEncoding' => 'string',
			'encoding' => 'string',
			'version' => 'string',
		],
		\DOMNamedNodeMap::class => [
			'length' => 'int',
		],
		\DOMNode::class => [
			'nodeName' => 'string',
			'nodeValue' => 'string',
			'nodeType' => 'int',
			'parentNode' => \DOMNode::class,
			'childNodes' => \DOMNodeList::class,
			'firstChild' => \DOMNode::class,
			'lastChild' => \DOMNode::class,
			'previousSibling' => \DOMNode::class,
			'nextSibling' => \DOMNode::class,
			'attributes' => \DOMNamedNodeMap::class,
			'ownerDocument' => \DOMDocument::class,
			'namespaceURI' => 'string',
			'prefix' => 'string',
			'localName' => 'string',
			'baseURI' => 'string',
			'textContent' => 'string',
		],
		\DOMNodeList::class => [
			'length' => 'int',
		],
		\DOMNotation::class => [ // extends DOMNode
			'publicId' => 'string',
			'systemId' => 'string',
		],
		\DOMProcessingInstruction::class => [ // extends DOMNode
			'target' => 'string',
			'data' => 'string',
		],
		\DOMText::class => [ // extends DOMCharacterData
			'wholeText' => 'string',
		],
		\DOMXPath::class => [ // extends DOMCharacterData
			'document' => \DOMDocument::class,
		],
		\XMLReader::class => [
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
		\ZipArchive::class => [
			'status' => 'int',
			'statusSys' => 'int',
			'numFiles' => 'int',
			'filename' => 'string',
			'comment' => 'string',
		],
		\LibXMLError::class => [
			'level' => 'int',
			'code' => 'int',
			'column' => 'int',
			'message' => 'string',
			'file' => 'string',
			'line' => 'int',
		],
	];

	/** @var string[][] */
	private static $properties71 = [
		\DateInterval::class => [
			'f' => 'float',
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
		if (PHP_VERSION_ID >= 70100) { // since PHP 7.1
			$this->properties = array_merge_recursive($this->properties, self::$properties71);
		}
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

	/**
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @param string $propertyName
	 * @return \PHPStan\Reflection\ClassReflection|null
	 */
	private function getClassWithProperties(ClassReflection $classReflection, string $propertyName)
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
