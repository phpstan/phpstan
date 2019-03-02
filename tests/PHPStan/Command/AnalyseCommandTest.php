<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Tester\CommandTester;

class AnalyseCommandTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @param string $dir
	 * @param string $file
	 * @dataProvider autoDiscoveryPathsProvider
	 */
	public function testConfigurationAutoDiscovery(string $dir, string $file): void
	{
		$originalDir = getcwd();
		if ($originalDir === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		chdir($dir);

		try {
			$output = $this->runCommand(1);
			$this->assertContains('Note: Using configuration file ' . $file . '.', $output);
		} catch (\Throwable $e) {
			chdir($originalDir);
			throw $e;
		}
	}

	public function testInvalidAutoloadFile(): void
	{
		$autoloadFile = 'phpstan.123456789.php';
		$dir = realpath(__DIR__ . '/../../../');

		$originalDir = getcwd();
		if ($originalDir === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		chdir($dir);

		try {
			$output = $this->runCommand(1, ['--autoload-file' => $autoloadFile]);
			$this->assertEquals(sprintf('Autoload file "%s" not found.' . PHP_EOL, $dir . DIRECTORY_SEPARATOR . $autoloadFile), $output);
		} catch (\Throwable $e) {
			chdir($originalDir);
			throw $e;
		}
	}

	public function testValidAutoloadFile(): void
	{
		$autoloadFile = 'data/autoload-file.php';
		$dir = __DIR__;

		$originalDir = getcwd();
		if ($originalDir === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		chdir($dir);

		try {
			$output = $this->runCommand(0, ['--autoload-file' => $autoloadFile]);
			$this->assertContains('[OK] No errors', $output);
			$this->assertNotContains(sprintf('Autoload file "%s" not found.' . PHP_EOL, $dir . DIRECTORY_SEPARATOR . $autoloadFile), $output);
			$this->assertSame(SOME_CONSTANT_IN_AUTOLOAD_FILE, 'magic value');
		} catch (\Throwable $e) {
			chdir($originalDir);
			throw $e;
		}
	}

	/**
	 * @return string[][]
	 */
	public static function autoDiscoveryPathsProvider(): array
	{
		return [
			[
				__DIR__ . '/test-autodiscover',
				__DIR__ . DIRECTORY_SEPARATOR . 'test-autodiscover' . DIRECTORY_SEPARATOR . 'phpstan.neon',
			],
			[
				__DIR__ . '/test-autodiscover-dist',
				__DIR__ . DIRECTORY_SEPARATOR . 'test-autodiscover-dist' . DIRECTORY_SEPARATOR . 'phpstan.neon.dist',
			],
			[
				__DIR__ . '/test-autodiscover-priority',
				__DIR__ . DIRECTORY_SEPARATOR . 'test-autodiscover-priority' . DIRECTORY_SEPARATOR . 'phpstan.neon',
			],
		];
	}

	private function runCommand(int $expectedStatusCode, array $parameters = []): string
	{
		$commandTester = new CommandTester(new AnalyseCommand());

		$commandTester->execute([
			'paths' => [__DIR__ . DIRECTORY_SEPARATOR . 'test'],
		] + $parameters);

		$this->assertSame($expectedStatusCode, $commandTester->getStatusCode());

		return $commandTester->getDisplay();
	}

}
