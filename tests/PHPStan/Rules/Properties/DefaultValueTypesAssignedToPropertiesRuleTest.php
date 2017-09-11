<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class DefaultValueTypesAssignedToPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new DefaultValueTypesAssignedToPropertiesRule(new RuleLevelHelper($this->createBroker(), true, false, true));
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
