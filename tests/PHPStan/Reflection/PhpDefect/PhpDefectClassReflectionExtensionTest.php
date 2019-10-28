<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\VerbosityLevel;
use XMLReader;
use ZipArchive;

class PhpDefectClassReflectionExtensionTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @dataProvider dataDateIntervalProperties
	 * @dataProvider dataDatePeriodProperties
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
	 * @dataProvider dataLibXMLErrorProperties
	 * @param string $className
	 * @param string $declaringClassName
	 * @param array<string, mixed>  $data
	 */
	public function testProperties(string $className, string $declaringClassName, array $data): void
	{
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(false);
		foreach ($data as $propertyName => $typeDescription) {
			/** @var \PHPStan\Broker\Broker $broker */
			$broker = self::getContainer()->getByType(Broker::class);
			$classReflection = $broker->getClass($className);
			$this->assertTrue($classReflection->hasProperty($propertyName), sprintf('%s::$%s', $className, $propertyName));
			$propertyReflection = $classReflection->getProperty($propertyName, $scope);
			$this->assertInstanceOf(PhpDefectPropertyReflection::class, $propertyReflection);
			$this->assertSame($declaringClassName, $propertyReflection->getDeclaringClass()->getName());
			$this->assertSame($typeDescription, $propertyReflection->getReadableType()->describe(VerbosityLevel::precise()), sprintf('%s::$%s', $className, $propertyName));
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

	public function dataDatePeriodProperties(): array
	{
		return [
			[
				\DatePeriod::class,
				\DatePeriod::class,
				[
					'recurrences' => 'int',
					'include_start_date' => 'bool',
					'start' => \DateTimeInterface::class,
					'current' => \DateTimeInterface::class,
					'end' => \DateTimeInterface::class,
					'interval' => \DateInterval::class,
				],
			],
			[
				\PhpDefectClasses\DatePeriodChild::class,
				\DatePeriod::class,
				[
					'recurrences' => 'int',
					'include_start_date' => 'bool',
					'start' => \DateTimeInterface::class,
					'current' => \DateTimeInterface::class,
					'end' => \DateTimeInterface::class,
					'interval' => \DateInterval::class,
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
					'ownerDocument' => \DOMDocument::class,
					'ownerElement' => \DOMElement::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'config' => \DOMConfiguration::class,
					'doctype' => \DOMDocumentType::class . '|null',
					'documentElement' => 'DOMElement|null',
					'documentURI' => 'string',
					'encoding' => 'string',
					'formatOutput' => 'bool',
					'implementation' => \DOMImplementation::class,
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
			],
			[
				// inherited properties from DOMNode
				\DOMDocument::class,
				\DOMNode::class,
				[
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
					'entities' => \DOMNamedNodeMap::class,
					'notations' => \DOMNamedNodeMap::class,
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
					'ownerDocument' => 'DOMDocument|null',
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
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'ownerDocument' => \DOMDocument::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'parentNode' => \DOMNode::class,
					'childNodes' => \DOMNodeList::class,
					'firstChild' => \DOMNode::class,
					'lastChild' => \DOMNode::class,
					'previousSibling' => \DOMNode::class,
					'nextSibling' => \DOMNode::class,
					'attributes' => \DOMNamedNodeMap::class,
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
					'ownerDocument' => \DOMDocument::class,
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
					'document' => \DOMDocument::class,
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
		if (!class_exists('ZipArchive')) {
			return [];
		}

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

	public function dataLibXMLErrorProperties(): array
	{
		return [
			[
				\LibXMLError::class,
				\LibXMLError::class,
				[
					'level' => 'int',
					'code' => 'int',
					'column' => 'int',
					'message' => 'string',
					'file' => 'string',
					'line' => 'int',
				],
			],
			[
				'\libXMLError',
				\LibXMLError::class,
				[
					'level' => 'int',
					'code' => 'int',
					'column' => 'int',
					'message' => 'string',
					'file' => 'string',
					'line' => 'int',
				],
			],
		];
	}

	/**
	 * @dataProvider dataDateInterval71Properties
	 * @param string $className
	 * @param string $declaringClassName
	 * @param array<string, mixed> $data
	 */
	public function test71Properties(string $className, string $declaringClassName, array $data): void
	{
		$this->testProperties($className, $declaringClassName, $data);
	}

	public function dataDateInterval71Properties(): array
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
					'f' => 'float',
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
					'f' => 'float',
					'invert' => 'int',
					'days' => 'mixed',
				],
			],
		];
	}

}
