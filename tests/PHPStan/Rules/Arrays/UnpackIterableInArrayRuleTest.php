<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class UnpackIterableInArrayRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UnpackIterableInArrayRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testRule(): void
	{
		if (PHP_VERSION_ID < 70400) {
			$this->markTestSkipped('Test requires PHP 7.4.');
		}
		$this->analyse([__DIR__ . '/data/unpack-iterable.php'], [
			[
				'Only iterables can be unpacked, array<int>|null given.',
				21,
			],
			[
				'Only iterables can be unpacked, int given.',
				22,
			],
			[
				'Only iterables can be unpacked, string given.',
				23,
			],
		]);
	}

}
