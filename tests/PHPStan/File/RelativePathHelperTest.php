<?php declare(strict_types = 1);

namespace PHPStan\File;

class RelativePathHelperTest extends \PHPUnit\Framework\TestCase
{

	public function dataGetRelativePath(): array
	{
		return [
			[
				'/usr',
				[],
				'/usr/app/test.php',
				'app/test.php',
			],
			[
				'',
				['/usr'],
				'/usr/app/test.php',
				'app/test.php',
			],
			[
				'/',
				['/usr'],
				'/usr/app/test.php',
				'app/test.php',
			],
			[
				'',
				[],
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/var',
				[],
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/var',
				['/usr'],
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/',
				[],
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/',
				[
					'/usr/app/src',
					'/usr/app/tests',
				],
				'/usr/app/src/test.php',
				'src/test.php',
			],
			[
				'/',
				[
					'/usr/app/src',
					'/usr/app/tests',
				],
				'/usr/app/tests/test.php',
				'tests/test.php',
			],
			[
				'/usr',
				[
					'/usr/app/src',
					'/usr/app/tests',
				],
				'/usr/app/src/test.php',
				'src/test.php',
			],
			[
				'/usr',
				[
					'/usr/app/src',
					'/',
				],
				'/usr/app/src/test.php',
				'/usr/app/src/test.php',
			],
			[
				'/usr',
				[
					'/usr/app/src',
					'/usr/app/tests',
				],
				'/usr/app/tests/test.php',
				'tests/test.php',
			],
			[
				'/',
				[
					'/usr/app/src/analyzed.php',
				],
				'/usr/app/src/analyzed.php',
				'analyzed.php',
			],
			[
				'/usr',
				[
					'/usr/app/src/analyzed.php',
				],
				'/usr/app/src/analyzed.php',
				'analyzed.php',
			],
			[
				'/usr/app',
				[
					'/usr/app/src/analyzed.php',
				],
				'/usr/app/src/analyzed.php',
				'analyzed.php',
			],
			[
				'/usr/app',
				[
					'/usr/app/src/analyzed.php',
					'/',
				],
				'/usr/app/src/analyzed.php',
				'/usr/app/src/analyzed.php',
			],
		];
	}

	/**
	 * @dataProvider dataGetRelativePath
	 * @param string $currentWorkingDirectory
	 * @param string[] $analysedPaths
	 * @param string $filenameToRelativize
	 * @param string $expectedResult
	 */
	public function testGetRelativePathOnUnix(
		string $currentWorkingDirectory,
		array $analysedPaths,
		string $filenameToRelativize,
		string $expectedResult
	): void
	{
		$helper = new RelativePathHelper($currentWorkingDirectory, '/', $analysedPaths);
		$this->assertSame(
			$expectedResult,
			$helper->getRelativePath($filenameToRelativize)
		);
	}

	/**
	 * @dataProvider dataGetRelativePath
	 * @param string $currentWorkingDirectory
	 * @param string[] $analysedPaths
	 * @param string $filenameToRelativize
	 * @param string $expectedResult
	 */
	public function testGetRelativePathOnWindows(
		string $currentWorkingDirectory,
		array $analysedPaths,
		string $filenameToRelativize,
		string $expectedResult
	): void
	{
		$sanitize = static function (string $path): string {
			if (substr($path, 0, 1) === '/') {
				return 'C:\\' . substr(str_replace('/', '\\', $path), 1);
			}

			return str_replace('/', '\\', $path);
		};
		$helper = new RelativePathHelper($sanitize($currentWorkingDirectory), '\\', array_map($sanitize, $analysedPaths));
		$this->assertSame(
			$sanitize($expectedResult),
			$helper->getRelativePath($sanitize($filenameToRelativize))
		);
	}

}
