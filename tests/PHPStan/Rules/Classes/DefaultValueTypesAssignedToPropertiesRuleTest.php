<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\Rule;

class DefaultValueTypesAssignedToPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new DefaultValueTypesAssignedToPropertiesRule();
	}

	public function testDefaultValueTypesAssignedToProperties()
	{
		$this->analyse([__DIR__ . '/data/properties-assigned-default-value-types.php'], [
			[
				'Property PropertiesAssignedDefaultValuesTypes\Foo::$stringPropertyWithWrongDefaultValue (string) does not accept default value of type int.',
				15,
			],
			[
				'Static property PropertiesAssignedDefaultValuesTypes\Foo::$staticStringPropertyWithWrongDefaultValue (string) does not accept default value of type int.',
				18,
			],
		]);
	}

}
