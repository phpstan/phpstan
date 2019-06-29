<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassesInTypehintsRule(new FunctionDefinitionCheck($broker, new ClassCaseSensitivityCheck($broker), true, false));
	}

	public function testExistingClassInTypehint(): void
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
			[
				'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::lorem() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
				28,
			],
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::lorem() has invalid type TestMethodTypehints\BazMethodTypehints.',
				28,
			],
			[
				'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::ipsum() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
				38,
			],
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::ipsum() has invalid type TestMethodTypehints\BazMethodTypehints.',
				38,
			],
			[
				'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::dolor() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
				48,
			],
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::dolor() has invalid type TestMethodTypehints\BazMethodTypehints.',
				48,
			],
			[
				'Parameter $parent of method TestMethodTypehints\FooMethodTypehints::parentWithoutParent() has invalid typehint type parent.',
				53,
			],
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::parentWithoutParent() has invalid type parent.',
				53,
			],
			[
				'Parameter $parent of method TestMethodTypehints\FooMethodTypehints::phpDocParentWithoutParent() has invalid typehint type parent.',
				62,
			],
			[
				'Return typehint of method TestMethodTypehints\FooMethodTypehints::phpDocParentWithoutParent() has invalid type parent.',
				62,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\fOOMethodTypehints.',
				67,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\fOOMethodTypehintS.',
				67,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\fOOMethodTypehints.',
				76,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\fOOMethodTypehintS.',
				76,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\FOOMethodTypehints.',
				85,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\FOOMethodTypehints.',
				85,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\FOOMethodTypehints.',
				94,
			],
			[
				'Class TestMethodTypehints\FooMethodTypehints referenced with incorrect case: TestMethodTypehints\FOOMethodTypehints.',
				94,
			],
			[
				'Parameter $array of method TestMethodTypehints\FooMethodTypehints::unknownTypesInArrays() has invalid typehint type TestMethodTypehints\NonexistentClass.',
				102,
			],
			[
				'Parameter $array of method TestMethodTypehints\FooMethodTypehints::unknownTypesInArrays() has invalid typehint type TestMethodTypehints\AnotherNonexistentClass.',
				102,
			],
		]);
	}

	public function testExistingClassInIterableTypehint(): void
	{
		$this->analyse([__DIR__ . '/data/typehints-iterable.php'], [
			[
				'Parameter $iterable of method TestMethodTypehints\IterableTypehints::doFoo() has invalid typehint type TestMethodTypehints\NonexistentClass.',
				11,
			],
			[
				'Parameter $iterable of method TestMethodTypehints\IterableTypehints::doFoo() has invalid typehint type TestMethodTypehints\AnotherNonexistentClass.',
				11,
			],
		]);
	}

}
