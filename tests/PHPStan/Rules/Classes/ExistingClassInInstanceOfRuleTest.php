<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\Rule;

class ExistingClassInInstanceOfRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new ExistingClassInInstanceOfRule($this->createBroker());
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
			]
		);
	}

}
