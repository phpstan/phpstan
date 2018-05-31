<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

class RawErrorFormatterTest extends TestBaseFormatter
{

	public function rawOutputProvider(): iterable
	{
		yield [
			'No errors',
			0,
			0,
			0,
			'',
		];

		yield [
			'One file error',
			1,
			1,
			0,
			'/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with "spaces" and unicode 😃.php:4:Foo
',
		];

		yield [
			'One generic error',
			1,
			0,
			1,
			'?:?:first generic error
',
		];

		yield [
			'Multiple file errors',
			1,
			4,
			0,
			'/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with "spaces" and unicode 😃.php:2:Bar
/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with "spaces" and unicode 😃.php:4:Foo
/data/folder/with space/and unicode 😃/project/foo.php:1:Foo
/data/folder/with space/and unicode 😃/project/foo.php:5:Bar
',
		];

		yield [
			'Multiple generic errors',
			1,
			0,
			2,
			'?:?:first generic error
?:?:second generic error
',
		];

		yield [
			'Multiple file, multiple generic errors',
			1,
			4,
			2,
			'?:?:first generic error
?:?:second generic error
/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with "spaces" and unicode 😃.php:2:Bar
/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with "spaces" and unicode 😃.php:4:Foo
/data/folder/with space/and unicode 😃/project/foo.php:1:Foo
/data/folder/with space/and unicode 😃/project/foo.php:5:Bar
',
		];
	}

	/**
	 * @param string $message          Test message
	 * @param int    $exitCode         Expected exit code from the application
	 * @param int    $numFileErrors    Number of errors for file
	 * @param int    $numGenericErrors Number of generic errors
	 * @param string $expected         Expected output
	 *
	 * @dataProvider rawOutputProvider
	 */
	public function testFormatErrors(
		string $message,
		int $exitCode,
		int $numFileErrors,
		int $numGenericErrors,
		string $expected
	): void
	{
		$formatter = new RawErrorFormatter();

		$this->assertSame($exitCode, $formatter->formatErrors(
			$this->getAnalysisResult($numFileErrors, $numGenericErrors),
			$this->getErrorConsoleStyle()
		), sprintf('%s: response code do not match', $message));

		$this->assertEquals($expected, $this->getOutputContent(), sprintf('%s: output do not match', $message));
	}

}
