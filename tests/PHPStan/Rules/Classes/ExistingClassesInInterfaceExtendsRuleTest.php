<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassesInInterfaceExtendsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassesInInterfaceExtendsRule(
			new ClassCaseSensitivityCheck($broker)
		);
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/extends-implements.php'],
			[
				[
					'Interface ExtendsImplements\FooInterface referenced with incorrect case: ExtendsImplements\FOOInterface.',
					30,
				],
			]
		);
	}

}
