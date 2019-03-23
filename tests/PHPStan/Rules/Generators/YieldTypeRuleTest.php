<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generators;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class YieldTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new YieldTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/yield.php'], [
			[
				'Generator expects value type int, string given.',
				14,
			],
			[
				'Generator expects key type string, int given.',
				15,
			],
			[
				'Generator expects value type int, null given.',
				15,
			],
			[
				'Generator expects key type string, int given.',
				16,
			],
			[
				'Generator expects key type string, int given.',
				17,
			],
			[
				'Generator expects value type int, string given.',
				17,
			],
		]);
	}

}
