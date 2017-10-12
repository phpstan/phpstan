<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassesInTypehintsRule(new FunctionDefinitionCheck($broker, new ClassCaseSensitivityCheck($broker), true, false));
	}

	public function testExistingClassInTypehint()
	{
		require_once __DIR__ . '/data/typehints.php';
		$this->analyse([__DIR__ . '/data/typehints.php'], [
			[
				'Return typehint of function TestFunctionTypehints\foo() has invalid type TestFunctionTypehints\NonexistentClass.',
				10,
			],
			[
				'Parameter $bar of function TestFunctionTypehints\bar() has invalid typehint type TestFunctionTypehints\BarFunctionTypehints.',
				15,
			],
			[
				'Return typehint of function TestFunctionTypehints\returnParent() has invalid type parent.',
				28,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\fOOFunctionTypehints.',
				33,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\fOOFunctionTypehintS.',
				33,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
				42,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
				42,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
				51,
			],
			[
				'Class TestFunctionTypehints\FooFunctionTypehints referenced with incorrect case: TestFunctionTypehints\FOOFunctionTypehints.',
				51,
			],
		]);
	}

}
