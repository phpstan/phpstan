<?php declare(strict_types = 1);

namespace PHPStan\Levels;

class LevelsIntegrationTest extends \PHPUnit\Framework\TestCase
{

	public function dataLevels(): array
	{
		return [
			[
				0,
				__DIR__ . '/data/Zero.php',
				__DIR__ . '/data/expected-0.json',
			],
		];
	}

	/**
	 * @dataProvider dataLevels
	 * @param int $level
	 * @param string $file
	 * @param string $expectedJsonFile
	 */
	public function testLevel(
		int $level,
		string $file,
		string $expectedJsonFile
	): void
	{
		$command = __DIR__ . '/../../../bin/phpstan';
		exec(sprintf('%s analyse --no-progress --errorFormat=prettyJson --level=%d --autoload-file %s %s', escapeshellcmd($command), $level, escapeshellarg($file), escapeshellarg($file)), $outputLines);
		$actualOutput = implode("\n", $outputLines);

		try {
			$this->assertJsonStringEqualsJsonFile(
				$expectedJsonFile,
				$actualOutput,
				sprintf('Level #%d - file %s', $level, pathinfo($file, PATHINFO_BASENAME))
			);
		} catch (\PHPUnit\Framework\AssertionFailedError $e) {
			file_put_contents(
				sprintf('%s/%s-actual.%s', pathinfo($expectedJsonFile, PATHINFO_DIRNAME), pathinfo($expectedJsonFile, PATHINFO_FILENAME), pathinfo($expectedJsonFile, PATHINFO_EXTENSION)),
				$actualOutput
			);
			throw $e;
		}
	}

}
