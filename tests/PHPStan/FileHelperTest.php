<?php declare(strict_types = 1);

namespace PHPStan;

class FileHelperTest extends \PHPStan\TestCase
{

	/**
	 * @return string[][]
	 */
	public function dataAbsolutizePathOnWindows(): array
	{
		return [
			['C:/Program Files', 'C:/Program Files'],
			['C:\Program Files', 'C:\Program Files'],
			['Program Files', 'C:\abcd\Program Files'],
			['/home/users', 'C:\abcd\home/users'],
			['users', 'C:\abcd\users'],
			['../lib', 'C:\abcd\../lib'],
			['./lib', 'C:\abcd\./lib'],
		];
	}

	/**
	 * @requires OS WIN
	 * @dataProvider dataAbsolutizePathOnWindows
	 * @param string $path
	 * @param string $absolutePath
	 */
	public function testAbsolutizePathOnWindows(string $path, string $absolutePath)
	{
		$fileHelper = new FileHelper('C:\abcd');
		$this->assertSame($absolutePath, $fileHelper->absolutizePath($path));
	}

	/**
	 * @return string[][]
	 */
	public function dataAbsolutizePathOnLinuxOrMac(): array
	{
		return [
			['C:/Program Files', '/abcd/C:/Program Files'],
			['C:\Program Files', '/abcd/C:\Program Files'],
			['Program Files', '/abcd/Program Files'],
			['/home/users', '/home/users'],
			['users', '/abcd/users'],
			['../lib', '/abcd/../lib'],
			['./lib', '/abcd/./lib'],
		];
	}

	/**
	 * @requires OS Linux|Mac
	 * @dataProvider dataAbsolutizePathOnLinuxOrMac
	 * @param string $path
	 * @param string $absolutePath
	 */
	public function testAbsolutizePathOnLinuxOrMac(string $path, string $absolutePath)
	{
		$fileHelper = new FileHelper('/abcd');
		$this->assertSame($absolutePath, $fileHelper->absolutizePath($path));
	}

	/**
	 * @return string[][]
	 */
	public function dataNormalizePathOnWindows(): array
	{
		return [
			['C:/Program Files/PHP', 'C:\Program Files\PHP'],
			['C:/Program Files/./PHP', 'C:\Program Files\PHP'],
			['C:/Program Files/../PHP', 'C:\PHP'],
			['/home/users/phpstan', '\home\users\phpstan'],
			['/home/users/./phpstan', '\home\users\phpstan'],
			['/home/users/../../phpstan/', '\phpstan'],
			['./phpstan/', 'phpstan'],
		];
	}

	/**
	 * @requires OS WIN
	 * @dataProvider dataNormalizePathOnWindows
	 * @param string $path
	 * @param string $normalizedPath
	 */
	public function testNormalizePathOnWindows(string $path, string $normalizedPath)
	{
		$this->assertSame($normalizedPath, $this->getContainer()->getByType(FileHelper::class)->normalizePath($path));
	}

	/**
	 * @return string[][]
	 */
	public function dataNormalizePathOnLinuxOrMac(): array
	{
		return [
			['C:\Program Files\PHP', 'C:/Program Files/PHP'],
			['C:\Program Files\.\PHP', 'C:/Program Files/PHP'],
			['C:\Program Files\..\PHP', 'C:/PHP'],
			['/home/users/phpstan', '/home/users/phpstan'],
			['/home/users/./phpstan', '/home/users/phpstan'],
			['/home/users/../../phpstan/', '/phpstan'],
			['./phpstan/', 'phpstan'],
		];
	}

	/**
	 * @requires OS Linux|Mac
	 * @dataProvider dataNormalizePathOnLinuxOrMac
	 * @param string $path
	 * @param string $normalizedPath
	 */
	public function testNormalizePathOnLinuxOrMac(string $path, string $normalizedPath)
	{
		$this->assertSame($normalizedPath, $this->getContainer()->getByType(FileHelper::class)->normalizePath($path));
	}

}
