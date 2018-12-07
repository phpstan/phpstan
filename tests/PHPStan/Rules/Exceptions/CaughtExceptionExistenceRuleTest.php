<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PHPStan\Rules\ClassCaseSensitivityCheck;

class CaughtExceptionExistenceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new CaughtExceptionExistenceRule(
			$broker,
			new ClassCaseSensitivityCheck($broker),
			true
		);
	}

	public function testCheckCaughtException(): void
	{
		$this->analyse(
			[__DIR__ . '/data/catch.php'],
			[
				[
					'Caught class TestCatch\FooCatch is not an exception.',
					17,
				],
				[
					'Caught class FooCatchException not found.',
					29,
				],
				[
					'Class TestCatch\MyCatchException referenced with incorrect case: TestCatch\MyCatchEXCEPTION.',
					41,
				],
			]
		);
	}

}
