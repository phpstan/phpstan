<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class RawErrorFormatterTest extends \PHPStan\Testing\TestCase
{

	private const DIRECTORY_PATH = '/data/folder/with space/and unicode 😃/project';

	/** @var RawErrorFormatter */
	protected $formatter;

	protected function setUp(): void
	{
		$this->formatter = new RawErrorFormatter();
	}

	public function testFormatErrors(): void
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);
		$analysisResultMock
			->expects($this->at(0))
			->method('hasErrors')
			->willReturn(true);

		$analysisResultMock
			->expects($this->once())
			->method('getNotFileSpecificErrors')
			->willReturn([
				'first generic error',
				'second generic error',
			]);

		$analysisResultMock
			->expects($this->once())
			->method('getFileSpecificErrors')
			->willReturn([
				new Error('Foo', self::DIRECTORY_PATH . '/foo.php', 1),
				new Error('Bar', self::DIRECTORY_PATH . '/file name with "spaces" and unicode 😃.php', 2),
			]);

		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource, OutputInterface::VERBOSITY_NORMAL, false);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(1, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '?:?:first generic error
?:?:second generic error
/data/folder/with space/and unicode 😃/project/foo.php:1:Foo
/data/folder/with space/and unicode 😃/project/file name with "spaces" and unicode 😃.php:2:Bar
';

		$this->assertEquals($expected, $this->rtrimMultiline($output));
	}

	public function testFormatErrorsEmpty(): void
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);
		$analysisResultMock
			->expects($this->at(0))
			->method('hasErrors')
			->willReturn(false);

		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource, OutputInterface::VERBOSITY_NORMAL, false);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(0, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '';
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
