<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

abstract class TestBaseProgressPrinter extends \PHPStan\Testing\TestCase
{

	/** @var StreamOutput */
	private $outputStream;

	/** @var ErrorsConsoleStyle */
	private $errorConsoleStyle;

	protected function setUp(): void
	{
		parent::setUp();

		$resource = fopen('php://memory', 'wb', false);
		if ($resource === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$this->outputStream = new StreamOutput($resource);

		$this->errorConsoleStyle = new ErrorsConsoleStyle(new StringInput(''), $this->outputStream);
	}

	protected function getOutputContent(): string
	{
		rewind($this->outputStream->getStream());

		$contents = stream_get_contents($this->outputStream->getStream());
		if ($contents === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->rtrimMultiline($contents);
	}

	protected function getErrorConsoleStyle(): ErrorsConsoleStyle
	{
		return $this->errorConsoleStyle;
	}

	private function rtrimMultiline(string $output): string
	{
		$result = array_map(static function (string $line): string {
			return rtrim($line, " \r\n");
		}, explode("\n", $output));

		return implode("\n", $result);
	}

}
