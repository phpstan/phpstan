<?php declare(strict_types = 1);

namespace PHPStan\Reflection\PhpDefect;

use PHPStan\Broker\Broker;
use ZipArchive;

class PhpDefectClassReflectionExtensionTest extends \PHPStan\TestCase
{

	public function dataZipArchiveProperties(): array
	{
		return [
			[
				'status',
				'int',
			],
			[
				'statusSys',
				'int',
			],
			[
				'numFiles',
				'int',
			],
			[
				'filename',
				'string',
			],
			[
				'comment',
				'string',
			],
		];
	}

	/**
	 * @dataProvider dataZipArchiveProperties
	 * @param string $propertyName
	 * @param string $typeDescription
	 */
	public function testZipArchiveProperties(string $propertyName, string $typeDescription)
	{
		$broker = $this->getContainer()->getByType(Broker::class);
		$zipArchiveReflection = $broker->getClass(ZipArchive::class);
		$this->assertTrue($zipArchiveReflection->hasProperty($propertyName));
		$propertyReflection = $zipArchiveReflection->getProperty($propertyName);
		$this->assertInstanceOf(PhpDefectPropertyReflection::class, $propertyReflection);
		$this->assertSame($zipArchiveReflection, $propertyReflection->getDeclaringClass());
		$this->assertSame($typeDescription, $propertyReflection->getType()->describe());
	}

}
