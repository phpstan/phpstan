<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use function chdir;
use function file_put_contents;
use function getcwd;

class BaselineNeonErrorFormatterIntegrationTest extends TestCase
{

	public function testErrorWithTrait(): void
	{
		$output = $this->runPhpStan(null);
		$errors = Json::decode($output, Json::FORCE_ARRAY);
		$this->assertSame(4, array_sum($errors['totals']));
		$this->assertCount(4, $errors['files']);
	}

	public function testGenerateBaselineAndRunAgainWithIt(): void
	{
		$output = $this->runPhpStan(null, 'baselineNeon');
		$baselineFile = sys_get_temp_dir() . '/baseline.neon';
		file_put_contents($baselineFile, $output);

		$output = $this->runPhpStan($baselineFile);
		@unlink($baselineFile);
		$errors = Json::decode($output, Json::FORCE_ARRAY);
		$this->assertSame(0, array_sum($errors['totals']));
		$this->assertCount(0, $errors['files']);
	}

	private function runPhpStan(?string $configFile, string $errorFormatter = 'json'): string
	{
		$originalDir = getcwd();
		if ($originalDir === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		chdir(__DIR__ . '/../../../..');
		exec(sprintf('%s %s analyse --no-progress --error-format=%s --level=7 %s %s', escapeshellarg(PHP_BINARY), 'bin/phpstan', $errorFormatter, $configFile !== null ? '--configuration ' . escapeshellarg($configFile) : '', escapeshellarg(__DIR__ . '/data/')), $outputLines);
		chdir($originalDir);

		return implode("\n", $outputLines);
	}

}
