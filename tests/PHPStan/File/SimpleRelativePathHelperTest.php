<?php declare(strict_types = 1);

namespace PHPStan\File;

class SimpleRelativePathHelperTest extends \PHPUnit\Framework\TestCase
{

	public function dataGetRelativePath(): array
	{
		return [
			[
				'/usr',
				'/',
				'/usr/app/test.php',
				'app/test.php',
			],
			[
				'',
				'/',
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/var',
				'/',
				'/usr/app/test.php',
				'/usr/app/test.php',
			],
			[
				'/usr/app',
				'/',
				'/usr/app/src/test.php',
				'src/test.php',
			],
			[
				'/',
				'/',
				'/usr/app/test.php',
				'usr/app/test.php',
			],
			[
				'/usr/',
				'/',
				'/usr/app/test.php',
				'app/test.php',
			],
			[
				'/usr/app',
				'/',
				'/usr/application/test.php',
				'/usr/application/test.php',
			],
			[
				'C:\\app',
				'\\',
				'C:\\app\\test.php',
				'test.php',
			],
			[
				'C:\\app\\',
				'\\',
				'C:\\app\\src\\test.php',
				'src\\test.php',
			],
		];
	}

	/**
	 * @dataProvider dataGetRelativePath
	 * @param string $currentWorkingDirectory
	 * @param string $directorySeparator
	 * @param string $filenameToRelativize
	 * @param string $expectedResult
	 */
	public function testGetRelativePathOnUnix(
		string $currentWorkingDirectory,
		string $directorySeparator,
		string $filenameToRelativize,
		string $expectedResult
	): void
	{
		$helper = new SimpleRelativePathHelper($currentWorkingDirectory, $directorySeparator);
		$this->assertSame(
			$expectedResult,
			$helper->getRelativePath($filenameToRelativize)
		);
	}

}
