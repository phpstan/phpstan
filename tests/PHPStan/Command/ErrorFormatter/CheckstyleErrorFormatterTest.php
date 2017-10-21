<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class CheckstyleErrorFormatterTest extends \PHPStan\Testing\TestCase
{

	private const DIRECTORY_PATH = '/data/folder/with space/and unicode 😃/project';

	/** @var CheckstyleErrorFormatter */
	protected $formatter;

	protected function setUp(): void
	{
		$this->formatter = new CheckstyleErrorFormatter();
	}

	public function testFormatErrors(): void
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);
		$analysisResultMock
			->expects($this->at(0))
			->method('hasErrors')
			->willReturn(true);

		$analysisResultMock
			->expects($this->at(1))
			->method('getFileSpecificErrors')
			->willReturn([
				new Error('Foo', self::DIRECTORY_PATH . '/foo.php', 1),
				new Error('Bar', self::DIRECTORY_PATH . '/file name with "spaces" and unicode 😃.php', 2),
			]);

		$analysisResultMock
			->expects($this->any())
			->method('getCurrentDirectory')
			->willReturn(self::DIRECTORY_PATH);

		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource);

		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);
		$this->assertSame(1, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
<file name="foo.php">
 <error line="1" column="1" severity="error" message="Foo"/>
</file>
<file name="file name with &quot;spaces&quot; and unicode 😃.php">
 <error line="2" column="1" severity="error" message="Bar"/>
</file>
</checkstyle>
';
		$this->assertXmlStringEqualsXmlString($expected, $output);
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

		$outputStream = new StreamOutput($resource);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertSame(0, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
</checkstyle>
';
		$this->assertXmlStringEqualsXmlString($expected, $output);
	}

}
