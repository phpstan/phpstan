<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\RuleLevelHelper;

class CallToCountOnlyWithArrayOrCountableRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToCountOnlyWithArrayOrCountableRule(new RuleLevelHelper(true));
	}

	public function testCallToCountOnlyWithArrayOrCountable()
	{
		$this->analyse([__DIR__ . '/data/count.php'], [
			[
				'Call to function count() with argument type string will always result in number 1.',
				23,
			],
			[
				'Call to function count() with argument type CountFunction\Foo will always result in number 1.',
				36,
			],
			[
				'Call to function count() with argument type CountFunction\BarCountable|CountFunction\Foo will always result in number 1.',
				44,
			],
		]);
	}

}
