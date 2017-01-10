<?php declare(strict_types = 1);

namespace PHPStan;

class FileHelperTest extends \PHPStan\TestCase
{

	/** @var \PHPStan\FileHelper */
	private $fileHelper;

	protected function setUp()
	{
		$this->fileHelper = new FileHelper();
	}

	/**
	 * @return mixed[][]
	 */
	public function dataIsAbsolute(): array
	{
		return [
			['C:/Program Files', true],
			['Program Files', false],
			['/home/users', true],
			['users', false],
			['../lib', false],
			['./lib', false],
		];
	}

	/**
	 * @dataProvider dataIsAbsolute
	 * @param string $path
	 * @param bool $isAbsolute
	 */
	public function testIsAbsolute(string $path, bool $isAbsolute)
	{
		$this->assertSame($isAbsolute, $this->fileHelper->isAbsolutePath($path));
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
		$this->assertSame($normalizedPath, $this->fileHelper->normalizePath($path));
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
		$this->assertSame($normalizedPath, $this->fileHelper->normalizePath($path));
	}

}
