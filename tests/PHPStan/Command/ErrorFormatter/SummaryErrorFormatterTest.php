<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use PHPStan\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class SummaryErrorFormatterTest extends TestCase
{

	/**
	 * @var SummaryErrorFormatter
	 */
	protected $formatter;

	/**
	 * Set up method
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->formatter = new SummaryErrorFormatter();
	}

	/**
	 * Test FormatErrors method
	 *
	 * @return void
	 */
	public function testFormatErrors()
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);
		$analysisResultMock
			->expects($this->at(0))
			->method('hasErrors')
			->willReturn(true);

		$analysisResultMock
			->expects($this->at(1))
			->method('getNotFileSpecificErrors')
			->willReturn(['not file specific error']);

		$analysisResultMock
			->expects($this->at(2))
			->method('getFileSpecificErrors')
			->willReturn([
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
				new Error('', '', 1, 'rule'),
			]);

		$outputStream = new StreamOutput(fopen('php://memory', 'w', false));
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(1, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '10 rule
 1 unspecified
';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test FormatErrors method
	 *
	 * @return void
	 */
	public function testFormatErrorsEmpty()
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);
		$analysisResultMock
			->expects($this->at(0))
			->method('hasErrors')
			->willReturn(false);

		$outputStream = new StreamOutput(fopen('php://memory', 'w', false));
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertEquals(0, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '';
		$this->assertEquals($expected, $output);
	}

}
