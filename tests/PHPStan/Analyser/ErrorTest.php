<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ErrorTest extends \PHPStan\TestCase
{

	public function testError()
	{
		$error = new Error('Message', 'file', 10);
		$this->assertSame('Message', $error->getMessage());
		$this->assertSame('file', $error->getFile());
		$this->assertSame(10, $error->getLine());
		$this->assertSame(
			'Message in file on line 10',
			(string) $error
		);
	}

	public function testErrorToStringWithoutLine()
	{
		$error = new Error('Message', 'file');
		$this->assertSame(
			'Message in file',
			(string) $error
		);
	}

	public function testErrorTrimTrailingDotFromMessage()
	{
		$error = new Error('Function does not exist.', 'file');
		$this->assertSame(
			'Function does not exist in file',
			(string) $error
		);
	}

}
