<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\TypehintHelper;

class PhpDefectClassReflectionExtension implements PropertiesClassReflectionExtension
{

	/** @var string[][] */
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
