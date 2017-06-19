<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

class CaughtExceptionExistenceRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CaughtExceptionExistenceRule(
			$this->createBroker()
		);
	}

	public function testCheckCaughtException()
	{
		$this->analyse([__DIR__ . '/data/catch.php'], [
			[
				'Caught class TestCatch\FooCatch is not an exception.',
				17,
			],
			[
				'Caught class FooCatchException not found.',
				29,
			],
		]);
	}

}
