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
		$originalDir = \getcwd();
		if ($originalDir === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		\chdir($dir);

		try {
			$output = $this->runCommand(1);
			$this->assertContains('Note: Using configuration file ' . $file . '.', $output);
		} catch (\Throwable $e) {
			\chdir($originalDir);
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
				__DIR__ . \DIRECTORY_SEPARATOR . 'test-autodiscover' . \DIRECTORY_SEPARATOR . 'phpstan.neon',
			],
			[
				__DIR__ . '/test-autodiscover-dist',
				__DIR__ . \DIRECTORY_SEPARATOR . 'test-autodiscover-dist' . \DIRECTORY_SEPARATOR . 'phpstan.neon.dist',
			],
			[
				__DIR__ . '/test-autodiscover-priority',
				__DIR__ . \DIRECTORY_SEPARATOR . 'test-autodiscover-priority' . \DIRECTORY_SEPARATOR . 'phpstan.neon',
			],
		];
	}

	private function runCommand(int $expectedStatusCode): string
	{
		$commandTester = new CommandTester(new AnalyseCommand());

		$commandTester->execute(
			[
				'paths' => [__DIR__ . \DIRECTORY_SEPARATOR . 'test'],
			]
		);

		$this->assertSame($expectedStatusCode, $commandTester->getStatusCode());

		return $commandTester->getDisplay();
	}

}
