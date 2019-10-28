<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Tester\CommandTester;
use const DIRECTORY_SEPARATOR;

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
			$this->assertStringContainsString('Note: Using configuration file ' . $file . '.', $output);
		} catch (\Throwable $e) {
			chdir($originalDir);
			throw $e;
		}
	}

	public function testInvalidAutoloadFile(): void
	{
		$dir = realpath(__DIR__ . '/../../../');
		$autoloadFile = $dir . DIRECTORY_SEPARATOR . 'phpstan.123456789.php';

		$output = $this->runCommand(1, ['--autoload-file' => $autoloadFile]);
		$this->assertSame(sprintf('Autoload file "%s" not found.' . PHP_EOL, $autoloadFile), $output);
	}

	public function testValidAutoloadFile(): void
	{
		$autoloadFile = __DIR__ . DIRECTORY_SEPARATOR . 'data/autoload-file.php';

		$output = $this->runCommand(0, ['--autoload-file' => $autoloadFile]);
		$this->assertStringContainsString('[OK] No errors', $output);
		$this->assertStringNotContainsString(sprintf('Autoload file "%s" not found.' . PHP_EOL, $autoloadFile), $output);
		$this->assertSame('magic value', SOME_CONSTANT_IN_AUTOLOAD_FILE);
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

	/**
	 * @param int $expectedStatusCode
	 * @param array<string, string> $parameters
	 * @return string
	 */
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
