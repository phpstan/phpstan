<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInClosureTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ExistingClassesInClosureTypehintsRule(new FunctionDefinitionCheck($this->createBroker()));
	}

	public function testExistingClassInTypehint()
	{
		$this->analyse([__DIR__ . '/data/closure-typehints.php'], [
			[
				'Return typehint of anonymous function has invalid type TestClosureFunctionTypehints\NonexistentClass.',
				10,
			],
			[
				'Parameter $bar of anonymous function has invalid typehint type TestClosureFunctionTypehints\BarFunctionTypehints.',
				15,
			],
		]);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testValidTypehint()
	{
		$this->analyse([__DIR__ . '/data/closure-valid-php71-typehints.php'], []);
	}

}
