<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\MissingTypehintCheck;

class MissingMethodReturnTypehintRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MissingMethodReturnTypehintRule(new MissingTypehintCheck());
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/missing-method-return-typehint.php'], [
			[
				'Method MissingMethodReturnTypehint\FooInterface::getFoo() has no return typehint specified.',
				8,
			],
			[
				'Method MissingMethodReturnTypehint\FooParent::getBar() has no return typehint specified.',
				15,
			],
			[
				'Method MissingMethodReturnTypehint\Foo::getFoo() has no return typehint specified.',
				25,
			],
			[
				'Method MissingMethodReturnTypehint\Foo::getBar() has no return typehint specified.',
				33,
			],
			[
				'Method MissingMethodReturnTypehint\Foo::unionTypeWithUnknownArrayValueTypehint() return type has no value type specified in iterable type array.',
				46,
			],
		]);
	}

}
