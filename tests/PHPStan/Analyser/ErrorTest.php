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
	}

}
