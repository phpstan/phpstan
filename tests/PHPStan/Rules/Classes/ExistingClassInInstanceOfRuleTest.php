<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassInInstanceOfRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassInInstanceOfRule(
			$broker,
			new ClassCaseSensitivityCheck($broker),
			true
		);
	}

	public function testClassDoesNotExist()
	{
		$this->analyse(
			[
				__DIR__ . '/data/instanceof.php',
				__DIR__ . '/data/instanceof-defined.php',
			],
			[
				[
					'Class InstanceOfNamespace\Bar not found.',
					7,
				],
				[
					'Using self outside of class scope.',
					9,
				],
				[
					'Class InstanceOfNamespace\Foo referenced with incorrect case: InstanceOfNamespace\FOO.',
					13,
				],
			]
		);
	}

}
