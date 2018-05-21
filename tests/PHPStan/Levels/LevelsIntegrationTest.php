<?php declare(strict_types = 1);

namespace PHPStan\Levels;

class LevelsIntegrationTest extends \PHPUnit\Framework\TestCase
{

	public function dataLevels(): \Generator
	{
		$topics = [
			//'returnTypes',
			//'acceptTypes',
			//'methodCalls',
			//'propertyAccesses',
			//'constantAccesses',
			'variables',
			//'callableCalls',
			//'arrayDimFetches',
		];

		foreach (range(0, 7) as $level) {
			foreach ($topics as $topic) {
				yield [
					$level,
					sprintf(__DIR__ . '/data/%s.php', $topic),
					sprintf(__DIR__ . '/data/%d-%s.json', $level, $topic),
				];
			}
		}
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
			file_put_contents($expectedJsonFile, $actualOutput);
			throw $e;
		}
	}

}
