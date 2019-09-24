<?php declare(strict_types = 1);

namespace PHPStan\Rules\TooWideTypehints;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class TooWideMethodReturnTypehintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TooWideMethodReturnTypehintRule();
	}

	public function testPrivate(): void
	{
		$this->analyse([__DIR__ . '/data/tooWideMethodReturnType-private.php'], [
			[
				'Method TooWideMethodReturnType\Foo::bar() never returns string so it can be removed from the return typehint.',
				14,
			],
			[
				'Method TooWideMethodReturnType\Foo::baz() never returns null so it can be removed from the return typehint.',
				18,
			],
		]);
	}

	public function testPublicProtected(): void
	{
		$this->analyse([__DIR__ . '/data/tooWideMethodReturnType-public-protected.php'], [
			[
				'Method TooWideMethodReturnType\Bar::bar() never returns string so it can be removed from the return typehint.',
				14,
			],
			[
				'Method TooWideMethodReturnType\Bar::baz() never returns null so it can be removed from the return typehint.',
				18,
			],
		]);
	}

	public function testPublicProtectedWithInheritance(): void
	{
		$this->analyse([__DIR__ . '/data/tooWideMethodReturnType-public-protected-inheritance.php'], [
			[
				'Method TooWideMethodReturnType\Baz::baz() never returns null so it can be removed from the return typehint.',
				27,
			],
		]);
	}

}
