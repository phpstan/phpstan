<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassesInClassImplementsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassesInClassImplementsRule(
			new ClassCaseSensitivityCheck($broker)
		);
	}

	public function testRule()
	{
		$this->analyse([__DIR__ . '/data/extends-implements.php'], [
			[
				'Interface ExtendsImplements\FooInterface referenced with incorrect case: ExtendsImplements\FOOInterface.',
				15,
			],
		]);
	}

}
