<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

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
		require_once __DIR__ . '/data/typehints.php';
		$this->analyse(
			[__DIR__ . '/data/typehints.php'],
			[
				[
					'Return typehint of function TestFunctionTypehints\foo() has invalid type TestFunctionTypehints\NonexistentClass.',
					15,
				],
				[
					'Parameter $bar of function TestFunctionTypehints\bar() has invalid typehint type TestFunctionTypehints\BarFunctionTypehints.',
					20,
				],
				[
					'Return typehint of function TestFunctionTypehints\returnParent() has invalid type TestFunctionTypehints\parent.',
					33,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\fOOFunctionTypehints.',
					38,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\fOOFunctionTypehintS.',
					38,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
					47,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
					47,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
					56,
				],
				[
					'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
					56,
				],
				[
					'Parameter $trait of function TestFunctionTypehints\referencesTraitsInNative() has invalid typehint type TestFunctionTypehints\SomeTrait.',
					61,
				],
				[
					'Return typehint of function TestFunctionTypehints\referencesTraitsInNative() has invalid type TestFunctionTypehints\SomeTrait.',
					61,
				],
				[
					'Parameter $trait of function TestFunctionTypehints\referencesTraitsInPhpDoc() has invalid typehint type TestFunctionTypehints\SomeTrait.',
					70,
				],
				[
					'Return typehint of function TestFunctionTypehints\referencesTraitsInPhpDoc() has invalid type TestFunctionTypehints\SomeTrait.',
					70,
				],
			]
		);
	}

}
