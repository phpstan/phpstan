<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ExistingClassesInTypehintsRule(new FunctionDefinitionCheck($this->createBroker()));
	}

	public function testExistingClassInTypehint()
	{
		$this->analyse([__DIR__ . '/data/typehints.php'], [
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::foo() has invalid type TestMethodTypehints\NonexistentClass.',
				8,
			],
			[
				'Parameter $bar of method TestMethodTypehints\FooMethodTypehints::bar() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
				13,
			],
		]);
	}

}
