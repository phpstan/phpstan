<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FileTypeMapperTest extends \PHPStan\TestCase
{

	public function testAccepts()
	{
		$this->createBroker();
		$fileTypeMapper = new FileTypeMapper($this->getParser(), $this->createMock(\Nette\Caching\Cache::class));

		/** @var Type[] $typeMap */
		$typeMap = $fileTypeMapper->getTypeMap(__DIR__ . '/data/annotations.php');

		$expected = [
			'void' => 'void',
			'string' => 'string',
			'?float' => 'float|null',
			'?\stdClass' => 'stdClass|null',
			'?int|?float|?\stdClass' => 'float|int|stdClass|null',
			'\stdClass' => 'stdClass',
			'string|?int' => 'int|string|null',
			'Image' => 'Image',
			'float' => 'float',
		];

		$this->assertEquals(array_keys($expected), array_keys($typeMap));

		foreach ($typeMap as $typeString => $type) {
			$this->assertSame($expected[$typeString], $type->describe());
		}
	}

}
