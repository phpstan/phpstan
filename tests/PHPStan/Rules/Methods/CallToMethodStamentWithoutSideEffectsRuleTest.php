<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class CallToMethodStamentWithoutSideEffectsRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new CallToMethodStamentWithoutSideEffectsRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/method-call-statement-no-side-effects.php'], [
			[
				'Call to method DateTimeImmutable::modify() on a separate line has no effect.',
				15,
			],
			[
				'Call to static method DateTimeImmutable::createFromFormat() on a separate line has no effect.',
				16,
			],
		]);
	}

}
