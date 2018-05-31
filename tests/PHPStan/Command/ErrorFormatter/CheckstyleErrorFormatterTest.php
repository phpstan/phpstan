<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

class CheckstyleErrorFormatterTest extends TestBaseFormatter
{

	public function checkstyleOutputProvider(): iterable
	{
		yield [
			'No errors',
			0,
			0,
			0,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
</checkstyle>
',
		];

		yield [
			'One file error',
			1,
			1,
			0,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
<file name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
 <error line="4" column="1" severity="error" message="Foo"/>
</file>
</checkstyle>
',
		];

		yield [
			'One generic error',
			1,
			0,
			1,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
</checkstyle>
',
		];

		yield [
			'Multiple file errors',
			1,
			4,
			0,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
<file name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
 <error line="2" column="1" severity="error" message="Bar"/>
 <error line="4" column="1" severity="error" message="Foo"/>
</file>
<file name="foo.php">
 <error line="1" column="1" severity="error" message="Foo"/>
 <error line="5" column="1" severity="error" message="Bar"/>
</file>
</checkstyle>
',
		];

		yield [
			'Multiple generic errors',
			1,
			0,
			2,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
</checkstyle>
',
		];

		yield [
			'Multiple file, multiple generic errors',
			1,
			4,
			2,
			'<?xml version="1.0" encoding="UTF-8"?>
<checkstyle>
<file name="folder with unicode 😃/file name with &quot;spaces&quot; and unicode 😃.php">
 <error line="2" column="1" severity="error" message="Bar"/>
 <error line="4" column="1" severity="error" message="Foo"/>
</file>
<file name="foo.php">
 <error line="1" column="1" severity="error" message="Foo"/>
 <error line="5" column="1" severity="error" message="Bar"/>
</file>
</checkstyle>
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
	 * @dataProvider checkstyleOutputProvider
	 */
	public function testFormatErrors(
		string $message,
		int $exitCode,
		int $numFileErrors,
		int $numGenericErrors,
		string $expected
	): void
	{
		$formatter = new CheckstyleErrorFormatter();

		$this->assertSame($exitCode, $formatter->formatErrors(
			$this->getAnalysisResult($numFileErrors, $numGenericErrors),
			$this->getErrorConsoleStyle()
		), sprintf('%s: response code do not match', $message));

		$this->assertXmlStringEqualsXmlString($expected, $this->getOutputContent(), sprintf('%s: XML do not match', $message));
	}

}
