<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class InternalPathsDiscoveryTest extends \PHPStan\Testing\TestCase
{

	public function testDiscoverInternalPaths(): void
	{
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/composer-json-discovery/internal/src/manually-passed/',
		]);

		$internalFilePath = __DIR__ . '/data/composer-json-discovery/internal/src/files/foo.php';
		$externalFilePath = __DIR__ . '/data/composer-json-discovery/external/src/files/bar.php';

		$internalPaths = $internalScopeHelper->getInternalPaths($internalFilePath);
		$this->assertEquals([
			__DIR__ . '/data/composer-json-discovery/internal/src/manually-passed/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-0/foo/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-0/bar/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-0/bar2/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-4/foo/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-4/bar/',
			__DIR__ . '/data/composer-json-discovery/internal/src/psr-4/bar2/',
			__DIR__ . '/data/composer-json-discovery/internal/src/classmap/',
			__DIR__ . '/data/composer-json-discovery/internal/src/files/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-0/foo/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-0/bar/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-0/bar2/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-4/foo/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-4/bar/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/psr-4/bar2/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/classmap/',
			__DIR__ . '/data/composer-json-discovery/internal/src/dev/files/',
		], $internalPaths);

		$this->assertTrue($internalScopeHelper->isFileInInternalPaths($internalFilePath));
		$this->assertFalse($internalScopeHelper->isFileInInternalPaths($externalFilePath));
	}

	public function testInvalidJsonFile(): void
	{
		$internalScopeHelper = new InternalScopeHelper([]);

		$filePath = __DIR__ . '/data/composer-json-discovery/invalid/foo.php';
		$internalPaths = $internalScopeHelper->getInternalPaths($filePath);

		$this->assertEmpty($internalPaths);
	}

}
