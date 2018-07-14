<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ErrorTest extends \PHPStan\Testing\TestCase
{

	public function testError(): void
	{
		$error = new Error('Message', 'file', 10);
		self::assertSame('Message', $error->getMessage());
		self::assertSame('file', $error->getFile());
		self::assertSame(10, $error->getLine());
	}

}
