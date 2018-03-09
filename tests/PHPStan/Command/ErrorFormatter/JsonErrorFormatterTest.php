<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use PHPStan\Testing\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class JsonErrorFormatterTest extends TestCase
{

	/**
	 * @var \PHPStan\Command\ErrorFormatter\JsonErrorFormatter
	 */
	private $formatter;

	protected function setUp(): void
	{
		parent::setUp();

		$this->formatter = new JsonErrorFormatter();
	}

	public function testFormatErrors(): void
	{
		$analysisResult = new AnalysisResult([
			new Error('Foo', 'foo.php', 1),
			new Error('Bar', 'file name with "spaces" and unicode 😃.php', 2),
		], [], true, '.');

		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertSame(1, $this->formatter->formatErrors($analysisResult, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '
{
	"totals":{
		"errors":0,
		"file_errors":2
	},
	"files":{
		"foo.php":{
			"errors":1,
			"messages":[
				{
					"message":"Foo",
					"line":1,
					"ignorable":true
				}
			]
		},
		"file name with \"spaces\" and unicode 😃.php":{
			"errors":1,
			"messages":[
				{
					"message":"Bar",
					"line":2,
					"ignorable":true
				}
			]
		}
	},
	"errors": []
}
';
		$this->assertJsonStringEqualsJsonString($expected, $output);
	}

	public function testFormatErrorsEmpty(): void
	{
		$analysisResult = new AnalysisResult([], [], true, '.');
		$resource = fopen('php://memory', 'w', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$outputStream = new StreamOutput($resource);
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertSame(0, $this->formatter->formatErrors($analysisResult, $style));

		rewind($outputStream->getStream());
		$output = stream_get_contents($outputStream->getStream());

		$expected = '
{
	"totals":{
		"errors":0,
		"file_errors":0
	},
	"files":[],
	"errors": []
}
';
		$this->assertJsonStringEqualsJsonString($expected, $output);
	}

}
