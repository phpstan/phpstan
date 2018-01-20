<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\RuleLevelHelper;

class CallToCountOnlyWithArrayOrCountableRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToCountOnlyWithArrayOrCountableRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testCallToCountOnlyWithArrayOrCountable()
	{
		$this->analyse([__DIR__ . '/data/count.php'], [
			[
				'Call to function count() expects argument type of array|Countable, string will always result in number 1.',
				23,
			],
			[
				'Call to function count() expects argument type of array|Countable, CountFunction\Foo will always result in number 1.',
				36,
			],
			[
				'Call to function count() expects argument type of array|Countable, CountFunction\BarCountable|CountFunction\Foo will always result in number 1.',
				44,
			],
			[
				'Call to function count() expects argument type of array|Countable, null will always result in number 0.',
				52,
			],
			[
				'Call to function count() expects argument type of array|Countable, int|null will always result in number 0 or 1.',
				60,
			],
		]);
	}

}
