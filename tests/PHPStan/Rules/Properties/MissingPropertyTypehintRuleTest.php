<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\MissingTypehintCheck;

class MissingPropertyTypehintRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MissingPropertyTypehintRule(new MissingTypehintCheck());
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/missing-property-typehint.php'], [
			[
				'Property MissingPropertyTypehint\MyClass::$prop1 has no typehint specified.',
				7,
			],
			[
				'Property MissingPropertyTypehint\MyClass::$prop2 has no typehint specified.',
				9,
			],
			[
				'Property MissingPropertyTypehint\MyClass::$prop3 has no typehint specified.',
				14,
			],
			[
				'Property MissingPropertyTypehint\ChildClass::$unionProp type has no value type specified in iterable type array.',
				32,
			],
		]);
	}

}
