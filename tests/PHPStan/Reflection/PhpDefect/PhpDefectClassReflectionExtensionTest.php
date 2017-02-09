<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Broker\Broker;
use XMLReader;
use ZipArchive;

class PhpDefectClassReflectionExtensionTest extends \PHPStan\TestCase
{

	/**
	 * @dataProvider dataDateIntervalProperties
	 * @dataProvider dataDomDocumentProperties
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
