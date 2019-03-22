<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generators;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class YieldFromTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new YieldFromTypeRule(new RuleLevelHelper($this->createBroker(), true, false, true), true);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/yield-from.php'], [
			[
				'Argument of an invalid type int passed to yield from, only iterables are supported.',
				15,
			],
			[
				'Generator expects key type DateTimeImmutable, stdClass given.',
				16,
			],
			[
				'Generator expects value type string, int given.',
				16,
			],
		]);
	}

}
