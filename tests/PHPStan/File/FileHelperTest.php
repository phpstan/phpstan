<?php declare(strict_types = 1);

namespace PHPStan\File;

class FileHelperTest extends \PHPStan\TestCase
{
    private function skipIfNotOnWindows()
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped();
        }
    }

    private function skipIfNotOnUnix()
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->markTestSkipped();
        }
    }

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
     * @dataProvider dataAbsolutizePathOnWindows
     * @param string $path
     * @param string $absolutePath
     */
    public function testAbsolutizePathOnWindows(string $path, string $absolutePath)
    {
        $this->skipIfNotOnWindows();
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
     * @dataProvider dataAbsolutizePathOnLinuxOrMac
     * @param string $path
     * @param string $absolutePath
     */
    public function testAbsolutizePathOnLinuxOrMac(string $path, string $absolutePath)
    {
        $this->skipIfNotOnUnix();
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
     * @dataProvider dataNormalizePathOnWindows
     * @param string $path
     * @param string $normalizedPath
     */
    public function testNormalizePathOnWindows(string $path, string $normalizedPath)
    {
        $this->skipIfNotOnWindows();
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
     * @dataProvider dataNormalizePathOnLinuxOrMac
     * @param string $path
     * @param string $normalizedPath
     */
    public function testNormalizePathOnLinuxOrMac(string $path, string $normalizedPath)
    {
        $this->skipIfNotOnUnix();
        $this->assertSame($normalizedPath, $this->getContainer()->getByType(FileHelper::class)->normalizePath($path));
    }
}
