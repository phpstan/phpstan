<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class CallToStaticMethodStamentWithoutSideEffectsRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new CallToStaticMethodStamentWithoutSideEffectsRule(
			new RuleLevelHelper($broker, true, false, true),
			$broker
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/static-method-call-statement-no-side-effects.php'], [
			[
				'Call to static method DateTimeImmutable::createFromFormat() on a separate line has no effect.',
				12,
			],
			[
				'Call to static method DateTimeImmutable::createFromFormat() on a separate line has no effect.',
				13,
			],
			[
				'Call to method DateTime::format() on a separate line has no effect.',
				23,
			],
		]);
	}

}
