<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\Rule;

class ExistingClassesInPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new ExistingClassesInPropertiesRule($this->createBroker());
	}

	public function testNonexistentClass()
	{
		$this->analyse(
			[
				__DIR__ . '/data/properties-types.php',
			],
			[
				[
					'Property PropertiesTypes\Foo::$bar has unknown class PropertiesTypes\Bar as its type.',
					12,
				],
				[
					'Property PropertiesTypes\Foo::$bars has unknown class PropertiesTypes\Bar[] as its array type.',
					18,
				],
			]
		);
	}

}
