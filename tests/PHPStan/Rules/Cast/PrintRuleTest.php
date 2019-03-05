<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\TestCase;

class PrintRuleTest extends TestCase
{

	public function testPrintRule(): void
	{
		$this->createTestBuilder()
			->setRules([
				new PrintRule(
					new RuleLevelHelper($this->createBroker(), true, false, true)
				),
			])
			->checkFile(__DIR__ . '/data/print.php');
	}

}
