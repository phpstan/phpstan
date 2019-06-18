<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class TooWidePrivateMethodReturnTypehintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TooWidePrivateMethodReturnTypehintRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/tooWidePrivateMethodReturnType.php'], [
			[
				'Private method TooWidePrivateMethodReturnType\Foo::bar() never returns string so it can be removed from the return typehint.',
				14,
			],
			[
				'Private method TooWidePrivateMethodReturnType\Foo::baz() never returns null so it can be removed from the return typehint.',
				18,
			],
		]);
	}

}
