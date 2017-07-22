<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Cache\Cache;

class FileTypeMapperTest extends \PHPStan\TestCase
{

	public function testAccepts()
	{
		$this->createBroker();
		$fileTypeMapper = new FileTypeMapper($this->getParser(), $this->createMock(Cache::class));

		/** @var Type[] $typeMap */
		$typeMap = $fileTypeMapper->getTypeMap(__DIR__ . '/data/annotations.php');

		$expected = [
			'int | float' => 'float|int',
			'void' => 'void',
			'string' => 'string',
			'?float' => 'float|null',
			'?\stdClass' => 'stdClass|null',
			'?int|?float|?\stdClass' => 'float|int|stdClass|null',
			'\stdClass' => 'stdClass',
			'string|?int' => 'int|string|null',
			'Image' => 'Image',
			'float' => 'float',
			'string | null' => 'string|null',
			'stdClass | null' => 'stdClass|null',
		];

		$this->assertEquals(array_keys($expected), array_keys($typeMap));

		foreach ($typeMap as $typeString => $type) {
			$this->assertSame($expected[$typeString], $type->describe());
		}
	}

}
