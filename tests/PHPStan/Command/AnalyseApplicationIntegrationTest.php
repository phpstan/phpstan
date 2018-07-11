<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Command\ErrorFormatter\TableErrorFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnalyseApplicationIntegrationTest extends \PHPStan\Testing\TestCase
{

	public function testExecuteOnADirectoryWithoutFiles(): void
	{
		$path = __DIR__ . '/test';
		@mkdir($path);
		chmod($path, 0777);
		$output = $this->runPath($path, 0);
		@unlink($path);

		$this->assertContains('No errors', $output);
	}

	public function testExecuteOnAFile(): void
	{
		$output = $this->runPath(__DIR__ . '/data/file-without-errors.php', 0);
		$this->assertContains('No errors', $output);
	}

	public function testExecuteOnANonExistentPath(): void
	{
		$path = __DIR__ . '/foo';
		$output = $this->runPath($path, 1);
		$this->assertContains(sprintf(
			'Path %s does not exist',
			$path
		), $output);
	}

	public function testExecuteOnAFileWithErrors(): void
	{
		$path = __DIR__ . '/../Rules/Functions/data/nonexistent-function.php';
		$output = $this->runPath($path, 1);
		$this->assertContains('Function foobarNonExistentFunction not found.', $output);
	}

	private function runPath(string $path, int $expectedStatusCode): string
	{
		$analyserApplication = self::getContainer()->getByType(AnalyseApplication::class);
		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$output = new StreamOutput($resource);

		$consoleStyle = new SymfonyStyle(
			$this->createMock(InputInterface::class),
			$output
		);
		$reportStyle = new SymfonyStyle(
			$this->createMock(InputInterface::class),
			$output
		);

		$memoryLimitFile = self::getContainer()->parameters['memoryLimitFile'];

		$statusCode = $analyserApplication->analyse(
			[$path],
			$consoleStyle,
			$reportStyle,
			new TableErrorFormatter(),
			false,
			false
		);
		if (file_exists($memoryLimitFile)) {
			unlink($memoryLimitFile);
		}
		$this->assertSame($expectedStatusCode, $statusCode);

		rewind($output->getStream());

		$contents = stream_get_contents($output->getStream());
		if ($contents === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $contents;
	}

}
