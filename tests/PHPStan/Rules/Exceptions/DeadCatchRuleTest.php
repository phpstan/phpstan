<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class DeadCatchRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new DeadCatchRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/dead-catch.php'], [
			[
				'Dead catch - TypeError is already caught by Throwable above.',
				27,
			],
		]);
	}

}
