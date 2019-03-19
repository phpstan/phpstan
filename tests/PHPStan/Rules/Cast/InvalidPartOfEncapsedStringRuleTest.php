<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PHPStan\Rules\RuleLevelHelper;

class InvalidPartOfEncapsedStringRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidPartOfEncapsedStringRule(
			new \PhpParser\PrettyPrinter\Standard(),
			new RuleLevelHelper($this->createBroker(), true, false, true)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-encapsed-part.php'], [
			[
				'Part $std (stdClass) of encapsed string cannot be cast to string.',
				8,
			],
		]);
	}

}
