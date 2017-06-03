<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Broker\Broker;
use XMLReader;
use ZipArchive;

class PhpDefectClassReflectionExtensionTest extends \PHPStan\TestCase
{

	/**
	 * @dataProvider dataDateIntervalProperties
	 * @dataProvider dataDomAttrProperties
	 * @dataProvider dataDomCharacterDataProperties
	 * @dataProvider dataDomDocumentProperties
	 * @dataProvider dataDomDocumentTypeProperties
	 * @dataProvider dataDomElementProperties
	 * @dataProvider dataDomEntityProperties
	 * @dataProvider dataDomNamedNodeMapProperties
	 * @dataProvider dataDomNodeListProperties
	 * @dataProvider dataDomNodeProperties
	 * @dataProvider dataDomNotationProperties
	 * @dataProvider dataDomTextProperties
	 * @dataProvider dataDomProcessingInstructionProperties
	 * @dataProvider dataDomXPathProperties
	 * @dataProvider dataXmlReaderProperties
	 * @dataProvider dataZipArchiveProperties
	 *
	 * @param string $className
	 * @param string $declaringClassName
	 * @param array  $data
	 */
	public function testProperties(string $className, string $declaringClassName, array $data)
	{
		foreach ($data as $propertyName => $typeDescription) {
			$broker = $this->getContainer()->getByType(Broker::class);
			$classReflection = $broker->getClass($className);
			$this->assertTrue($classReflection->hasProperty($propertyName), sprintf('%s::$%s', $className, $propertyName));
			$propertyReflection = $classReflection->getProperty($propertyName);
			$this->assertInstanceOf(PhpDefectPropertyReflection::class, $propertyReflection);
			$this->assertSame($declaringClassName, $propertyReflection->getDeclaringClass()->getName());
			$this->assertSame($typeDescription, $propertyReflection->getType()->describe(), sprintf('%s::$%s', $className, $propertyName));
		}
	}

	public function dataDateIntervalProperties(): array
	{
		return [
			[
				\DateInterval::class,
				\DateInterval::class,
				[
					'y' => 'int',
					'm' => 'int',
					'd' => 'int',
					'h' => 'int',
					'i' => 'int',
					's' => 'int',
					'invert' => 'int',
					'days' => 'mixed',
				],
			],
			[
				\PhpDefectClasses\DateIntervalChild::class,
				\DateInterval::class,
				[
					'y' => 'int',
					'm' => 'int',
					'd' => 'int',
					'h' => 'int',
					'i' => 'int',
					's' => 'int',
					'invert' => 'int',
					'days' => 'mixed',
				],
			],
		];
	}

	public function dataDomAttrProperties(): array
	{
		return [
			[
				\DOMAttr::class,
				\DOMAttr::class,
				[
					'name' => 'string',
					'ownerElement' => 'DOMElement',
					'schemaTypeInfo' => 'bool',
					'specified' => 'bool',
					'value' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMAttr::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomCharacterDataProperties(): array
	{
		return [
			[
				\DOMCharacterData::class,
				\DOMCharacterData::class,
				[
					'data' => 'string',
					'length' => 'int',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMCharacterData::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomDocumentProperties(): array
	{
		return [
			[
				\DOMDocument::class,
				\DOMDocument::class,
				[
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
			],
			[
				// inherited properties from DOMNode
				\DOMDocument::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomDocumentTypeProperties(): array
	{
		return [
			[
				\DOMDocumentType::class,
				\DOMDocumentType::class,
				[
					'publicId' => 'string',
					'systemId' => 'string',
					'name' => 'string',
					'entities' => 'DOMNamedNodeMap',
					'notations' => 'DOMNamedNodeMap',
					'internalSubset' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMDocumentType::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomElementProperties(): array
	{
		return [
			[
				\DOMElement::class,
				\DOMElement::class,
				[
					'schemaTypeInfo' => 'bool',
					'tagName' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMElement::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomEntityProperties(): array
	{
		return [
			[
				\DOMEntity::class,
				\DOMEntity::class,
				[
					'publicId' => 'string',
					'systemId' => 'string',
					'notationName' => 'string',
					'actualEncoding' => 'string',
					'encoding' => 'string',
					'version' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMEntity::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomNamedNodeMapProperties(): array
	{
		return [
			[
				\DOMNamedNodeMap::class,
				\DOMNamedNodeMap::class,
				[
					'length' => 'int',
				],
			],
		];
	}

	public function dataDomNodeListProperties(): array
	{
		return [
			[
				\DOMNodeList::class,
				\DOMNodeList::class,
				[
					'length' => 'int',
				],
			],
		];
	}

	public function dataDomNodeProperties(): array
	{
		return [
			[
				\DOMNode::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomNotationProperties(): array
	{
		return [
			[
				\DOMNotation::class,
				\DOMNotation::class,
				[
					'publicId' => 'string',
					'systemId' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMNotation::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomProcessingInstructionProperties(): array
	{
		return [
			[
				\DOMProcessingInstruction::class,
				\DOMProcessingInstruction::class,
				[
					'target' => 'string',
					'data' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMProcessingInstruction::class,
				\DOMNode::class,
				[
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
			],
		];
	}

	public function dataDomTextProperties(): array
	{
		return [
			[
				\DOMText::class,
				\DOMText::class,
				[
					'wholeText' => 'string',
				],
			],
			[
				// inherited properties from DOMNode
				\DOMText::class,
				\DOMNode::class,
				[
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
			],
			[
				// inherited properties from DOMCharacterData
				\DOMText::class,
				\DOMCharacterData::class,
				[
					'data' => 'string',
					'length' => 'int',
				],
			],
		];
	}

	public function dataDomXPathProperties(): array
	{
		return [
			[
				\DOMXPath::class,
				\DOMXPath::class,
				[
					'document' => 'DOMDocument',
				],
			],
		];
	}

	public function dataXmlReaderProperties(): array
	{
		return [
			[
				XMLReader::class,
				XMLReader::class,
				[
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
			],
		];
	}

	public function dataZipArchiveProperties(): array
	{
		return [
			[
				ZipArchive::class,
				ZipArchive::class,
				[
					'status' => 'int',
					'statusSys' => 'int',
					'numFiles' => 'int',
					'filename' => 'string',
					'comment' => 'string',
				],
			],
		];
	}

}
