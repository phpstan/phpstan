<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

class MinMaxFunctionReturnTypeExtensionTest extends \PHPStan\Testing\TestCase
{

	public function testMinMax(): void
	{
		$this->createTestBuilder()
			->checkFile(__DIR__ . '/data/min-max.php');
	}

}
