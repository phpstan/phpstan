<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class TableErrorFormatterTest extends \PHPStan\Testing\TestCase
{

	private const DIRECTORY_PATH = '/data/folder/with space/and unicode 😃/project';

	/** @var TableErrorFormatter */
	protected $formatter;

	protected function setUp(): void
	{
		$this->formatter = new TableErrorFormatter();
	}

	public function testFormatErrors(): void
	{
		$analysisResult = new AnalysisResult(
			[
				new Error('Foo', self::DIRECTORY_PATH . '/foo.php', 1),
				new Error('Bar', self::DIRECTORY_PATH . '/file name with "spaces" and unicode 😃.php', 2),
			],
			[
				'first generic error',
				'second generic error',
			],
			false,
			self::DIRECTORY_PATH
		);
		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource, OutputInterface::VERBOSITY_NORMAL, false);

		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(1, $this->formatter->formatErrors($analysisResult, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = ' ------ -------------------------------------------
  Line   file name with "spaces" and unicode 😃.php
 ------ -------------------------------------------
  2      Bar
 ------ -------------------------------------------

 ------ ---------
  Line   foo.php
 ------ ---------
  1      Foo
 ------ ---------

 ----------------------
  Error
 ----------------------
  first generic error
  second generic error
 ----------------------

 [ERROR] Found 4 errors

';

		$this->assertEquals($expected, $this->rtrimMultiline($output));
	}

	public function testFormatErrorsEmpty(): void
	{
		$analysisResult = new AnalysisResult([], [], false, self::DIRECTORY_PATH);
		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource, OutputInterface::VERBOSITY_NORMAL, false);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(0, $this->formatter->formatErrors($analysisResult, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '
 [OK] No errors

';
		$this->assertEquals($expected, $this->rtrimMultiline($output));
	}

	private function rtrimMultiline(string $output): string
	{
		$result = array_map(function (string $line): string {
			return rtrim($line, " \r\n");
		}, explode("\n", $output));

		return implode("\n", $result);
	}

}
