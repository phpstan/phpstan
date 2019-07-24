<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use Nette\Schema\Expect;
use Nette\Schema\Processor;
use PHPStan\Testing\TestCase;

class FunctionMetadataTest extends TestCase
{

	public function testSchema(): void
	{
		$data = require __DIR__ . '/../../../../src/Reflection/SignatureMap/functionMetadata.php';
		$this->assertIsArray($data);

		$processor = new Processor();
		$processor->process(Expect::arrayOf(
			Expect::structure([
				'hasSideEffects' => Expect::bool()->required(),
			])->required()
		)->required(), $data);
	}

}
