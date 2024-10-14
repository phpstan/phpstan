<?php declare(strict_types = 1);

namespace PhpunitBridgeApp;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyTest extends KernelTestCase
{

	public function testSomething(): void
	{
		$this->assertTrue(true); // @phpstan-ignore method.alreadyNarrowedType
	}

}
