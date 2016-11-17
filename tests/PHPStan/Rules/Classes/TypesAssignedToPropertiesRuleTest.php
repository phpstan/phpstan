<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class TypesAssignedToPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new TypesAssignedToPropertiesRule();
	}

	public function testTypesAssignedToProperties()
	{
		$this->analyse([__DIR__ . '/data/properties-assigned-types.php'], [
			[
				'Property PropertiesAssignedTypes\Foo::$stringProperty (string) does not accept int.',
				23,
			],
			[
				'Property PropertiesAssignedTypes\Foo::$intProperty (int) does not accept string.',
				25,
			],
			[
				'Property PropertiesAssignedTypes\Foo::$fooProperty (PropertiesAssignedTypes\Foo) does not accept PropertiesAssignedTypes\Bar.',
				27,
			],
			[
				'Static property PropertiesAssignedTypes\Foo::$staticStringProperty (string) does not accept int.',
				29,
			],
			[
				'Static property PropertiesAssignedTypes\Foo::$staticStringProperty (string) does not accept int.',
				31,
			],
			[
				'Static property PropertiesAssignedTypes\Ipsum::$parentStringProperty (string) does not accept int.',
				33,
			],
		]);
	}

}
