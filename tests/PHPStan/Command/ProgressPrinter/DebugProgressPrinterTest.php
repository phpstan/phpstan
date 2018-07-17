<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

class DebugProgressPrinterTest extends TestBaseProgressPrinter
{

	public function dataProgressPrinterProvider(): iterable
	{
		yield [
			[],
			'',
		];

		yield [
			[
				'/foo.php',
				'/bar.php',
				'/baz.php',
			],
			'/foo.php
/bar.php
/baz.php
',
		];
	}

	/**
	 * @dataProvider dataProgressPrinterProvider
	 *
	 * @param string[] $files
	 * @param string $expected
	 */
	public function testProgressPrinter(
		array $files,
		string $expected
	): void
	{
		$this->skipIfNotOnUnix();
		$progressPrinter = new DebugProgressPrinter();
		$progressPrinter->setOutputStyle($this->getErrorConsoleStyle());

		$progressPrinter->start(count($files));
		foreach ($files as $file) {
			$progressPrinter->beforeAnalyzingFile($file);
			$progressPrinter->afterAnalyzingFile($file, []);
		}
		$progressPrinter->finish();

		$this->assertSame($expected, $this->getOutputContent());
	}

}
