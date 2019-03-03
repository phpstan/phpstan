<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class AnonymousClassNameRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new AnonymousClassNameRule($broker);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/anonymous-class-name.php'], [
			[
				'found',
				6,
			],
		]);
	}

}
