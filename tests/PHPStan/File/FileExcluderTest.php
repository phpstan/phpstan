<?php declare(strict_types = 1);

namespace PHPStan\File;

class FileExcluderTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @dataProvider dataExcludeOnWindows
	 * @param string $filePath
	 * @param string[] $analyseExcludes
	 * @param bool $isExcluded
	 */
	public function testFilesAreExcludedFromAnalysingOnWindows(
		string $filePath,
		array $analyseExcludes,
		bool $isExcluded
	): void
	{
		$this->skipIfNotOnWindows();

		$fileExcluder = new FileExcluder($this->getFileHelper(), $analyseExcludes);

		self::assertSame($isExcluded, $fileExcluder->isExcludedFromAnalysing($filePath));
	}

	public function dataExcludeOnWindows(): array
	{
		return [
			[
				__DIR__ . '/data/excluded-file.php',
				[],
				false,
			],
			[
				__DIR__ . '/data/excluded-file.php',
				[__DIR__],
				true,
			],
			[
				__DIR__ . '\Foo\data\excluded-file.php',
				[__DIR__ . '/*/data/*'],
				true,
			],
			[
				__DIR__ . '\data\func-call.php',
				[],
				false,
			],
			[
				__DIR__ . '\data\parse-error.php',
				[__DIR__ . '/*'],
				true,
			],
			[
				__DIR__ . '\data\parse-error.php',
				[__DIR__ . '/data/?a?s?-error.?h?'],
				true,
			],
			[
				__DIR__ . '\data\parse-error.php',
				[__DIR__ . '/data/[pP]arse-[eE]rror.ph[pP]'],
				true,
			],
			[
				__DIR__ . '\data\parse-error.php',
				['tests/PHPStan/File/data'],
				true,
			],
			[
				__DIR__ . '\data\parse-error.php',
				[__DIR__ . '/aaa'],
				false,
			],
			[
				'C:\Temp\data\parse-error.php',
				['C:/Temp/*'],
				true,
			],
			[
				'C:\Data\data\parse-error.php',
				['C:/Temp/*'],
				false,
			],
			[
				'c:\Temp\data\parse-error.php',
				['C:/Temp/*'],
				true,
			],
			[
				'C:\Temp\data\parse-error.php',
				['C:/temp/*'],
				true,
			],
			[
				'c:\Data\data\parse-error.php',
				['C:/Temp/*'],
				false,
			],
		];
	}

	/**
	 * @dataProvider dataExcludeOnUnix
	 * @param string $filePath
	 * @param string[] $analyseExcludes
	 * @param bool $isExcluded
	 */
	public function testFilesAreExcludedFromAnalysingOnUnix(
		string $filePath,
		array $analyseExcludes,
		bool $isExcluded
	): void
	{
		$this->skipIfNotOnUnix();

		$fileExcluder = new FileExcluder($this->getFileHelper(), $analyseExcludes);

		self::assertSame($isExcluded, $fileExcluder->isExcludedFromAnalysing($filePath));
	}

	public function dataExcludeOnUnix(): array
	{
		return [
			[
				__DIR__ . '/data/excluded-file.php',
				[],
				false,
			],
			[
				__DIR__ . '/data/excluded-file.php',
				[__DIR__],
				true,
			],
			[
				__DIR__ . '/Foo/data/excluded-file.php',
				[__DIR__ . '/*/data/*'],
				true,
			],
			[
				__DIR__ . '/data/func-call.php',
				[],
				false,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/*'],
				true,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/data/?a?s?-error.?h?'],
				true,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/data/[pP]arse-[eE]rror.ph[pP]'],
				true,
			],
			[
				__DIR__ . '/data/parse-error.php',
				['tests/PHPStan/File/data'],
				true,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/aaa'],
				false,
			],
			[
				'/tmp/data/parse-error.php',
				['/tmp/*'],
				true,
			],
			[
				'/home/myname/data/parse-error.php',
				['/tmp/*'],
				false,
			],
		];
	}

}
