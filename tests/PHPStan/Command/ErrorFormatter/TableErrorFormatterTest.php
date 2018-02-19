<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class TableErrorFormatterTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @var TableErrorFormatter
	 */
	protected $formatter;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		$this->formatter = new TableErrorFormatter();
	}

	public function testErrorsAreDisplayedInAlphabeticalOrder(): void
	{
		$analysisResultMock = $this->createMock(AnalysisResult::class);

		$analysisResultMock
			->expects(self::any())
			->method('hasErrors')
			->willReturn(true);

		$analysisResultMock
			->expects(self::any())
			->method('getFileSpecificErrors')
			->willReturn([
				new Error('Message 1', 'b', 1),
				new Error('Message 2', 'a', 1),
				new Error('Message 3', 'd', 1),
				new Error('Message 4', 'c', 1),
				new Error('Message 5', '1', 1),
			]);

		$resource = fopen('php://memory', 'wb', false);

		self::assertInternalType('resource', $resource);

		$outputStream = new StreamOutput($resource);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		self::assertSame(1, $this->formatter->formatErrors($analysisResultMock, $style));

		rewind($outputStream->getStream());

		self::assertStringMatchesFormat(
			'%Aa%AMessage 2%A'
			. '%Ab%AMessage 1%A'
			. '%Ac%AMessage 4%A'
			. '%Ad%AMessage 3%A'
			. '%A1%AMessage 5%A',
			stream_get_contents($outputStream->getStream())
		);
	}

}
