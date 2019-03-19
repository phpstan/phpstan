<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class DeadForeachRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new DeadForeachRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/dead-foreach.php'], [
			[
				'Empty array passed to foreach.',
				16,
			],
			[
				'Empty array passed to foreach.',
				30,
			],
		]);
	}

}
