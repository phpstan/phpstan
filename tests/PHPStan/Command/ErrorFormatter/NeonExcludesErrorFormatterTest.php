<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;

class NeonExcludesErrorFormatterTest extends TestBaseFormatter
{

	public function dataFormatterOutputProvider(): iterable
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
			"parameters:
	ignoreErrors:
		- '''
#Foo#
'''
",
		];

		yield [
			'One generic error',
			1,
			0,
			1,
			"parameters:
	ignoreErrors:
		- '''
#first generic error#
'''
",
		];

		yield [
			'Multiple file errors',
			1,
			4,
			0,
			"parameters:
	ignoreErrors:
		- '''
#Bar#
'''
		- '''
#Foo#
'''
",
		];

		yield [
			'Multiple generic errors',
			1,
			0,
			2,
			"parameters:
	ignoreErrors:
		- '''
#first generic error#
'''
		- '''
#second generic error#
'''
",
		];

		yield [
			'Multiple file, multiple generic errors',
			1,
			4,
			2,
			"parameters:
	ignoreErrors:
		- '''
#first generic error#
'''
		- '''
#second generic error#
'''
		- '''
#Bar#
'''
		- '''
#Foo#
'''
",
		];
	}

	/**
	 * @dataProvider dataFormatterOutputProvider
	 *
	 * @param string $message
	 * @param int    $exitCode
	 * @param int    $numFileErrors
	 * @param int    $numGenericErrors
	 * @param string $expected
	 */
	public function testFormatErrors(
		string $message,
		int $exitCode,
		int $numFileErrors,
		int $numGenericErrors,
		string $expected
	): void
	{
		$formatter = new NeonExcludesErrorFormatter();

		$this->assertSame($exitCode, $formatter->formatErrors(
			$this->getAnalysisResult($numFileErrors, $numGenericErrors),
			$this->getErrorConsoleStyle()
		), sprintf('%s: response code do not match', $message));

		$this->assertEquals($expected, $this->getOutputContent(), sprintf('%s: output do not match', $message));
	}


	public function testFormatErrorMessagesRegexEscape(): void
	{
		$formatter = new NeonExcludesErrorFormatter();

		$result  = new AnalysisResult(
			[new Error('Escape Regex with file # ~ <> \' ()', 'Testfile')],
			['Escape Regex without file # ~ <> \' ()'],
			false,
			false,
			null
		);
		$formatter->formatErrors(
			$result,
			$this->getErrorConsoleStyle()
		);

		self::assertSame(
			"parameters:
	ignoreErrors:
		- '''
#Escape Regex without file \# ~ <\> ' \(\)#
'''
		- '''
#Escape Regex with file \# ~ <\> ' \(\)#
'''
",
			$this->getOutputContent()
		);
	}

}
