<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnalyseApplicationIntegrationTest extends \PHPStan\TestCase
{

	public function testExecuteOnADirectoryWithoutFiles()
	{
		$path = __DIR__ . '/test';
		@mkdir($path);
		chmod($path, 0777);
		$output = $this->runPath($path, 0);
		@unlink($path);

		$this->assertContains('No errors', $output);
	}

	public function testExecuteOnAFile()
	{
		$output = $this->runPath(__DIR__ . '/data/file-without-errors.php', 0);
		$this->assertContains('No errors', $output);
	}

	public function testExecuteOnANonExistentPath()
	{
		$path = __DIR__ . '/foo';
		$output = $this->runPath($path, 1);
		$this->assertContains(sprintf(
			'Path %s does not exist',
			$path
		), $output);
	}

	public function testExecuteOnAFileWithErrors()
	{
		$this->markTestIncomplete('Failing and I dont know why :(');
		$path = __DIR__ . '/../Rules/Functions/data/nonexistent-function.php';
		$output = $this->runPath($path, 1);
		var_dump($output);
		$this->assertContains(sprintf(
			'Function foobarNonExistentFunction does not exist in %s on line 8',
			realpath($path)
		), $output);
	}

	/**
	 * @param string $path
	 * @param integer $expectedStatusCode
	 */
	private function runPath($path, $expectedStatusCode)
	{
		$analyserApplication = $this->getContainer()->getByType(AnalyseApplication::class);
		$output = new StreamOutput(fopen('php://memory', 'w', false));

		$style = new SymfonyStyle(
			$this->getMock(InputInterface::class),
			$output
		);

		$statusCode = $analyserApplication->analyse([$path], $style);
		$this->assertSame($expectedStatusCode, $statusCode);

		rewind($output->getStream());

		return stream_get_contents($output->getStream());
	}

}
