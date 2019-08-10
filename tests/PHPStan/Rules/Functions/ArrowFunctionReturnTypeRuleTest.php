<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class ArrowFunctionReturnTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ArrowFunctionReturnTypeRule(new FunctionReturnTypeCheck(new RuleLevelHelper(
			$this->createBroker(),
			true,
			false,
			true
		)));
	}

	public function testRule(): void
	{
		if (PHP_VERSION_ID < 70400) {
			$this->markTestSkipped('Test requires PHP 7.4.');
		}
		$this->analyse([__DIR__ . '/data/arrow-functions-return-type.php'], [
			[
				'Anonymous function should return string but returns int.',
				12,
			],
			[
				'Anonymous function should return int but returns string.',
				14,
			],
		]);
	}

}
