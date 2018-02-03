<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ErrorTest extends \PHPStan\Testing\TestCase
{

	public function testError(): void
	{
		$error = new Error('Message', 'file', 10);
		$this->assertSame('Message', $error->getMessage());
		$this->assertSame('file', $error->getFile());
		$this->assertSame(10, $error->getLine());
	}

}
