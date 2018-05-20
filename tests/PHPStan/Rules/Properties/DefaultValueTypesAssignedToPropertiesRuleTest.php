<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class DefaultValueTypesAssignedToPropertiesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		return new DefaultValueTypesAssignedToPropertiesRule(new RuleLevelHelper($this->createBroker(), true, false, true));
	}

	public function testDefaultValueTypesAssignedToProperties(): void
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
			[
				'Static property PropertiesAssignedDefaultValuesTypes\Foo::$windowsNtVersions (array<string, string>) does not accept default value of type array<int|string, string>.',
				24,
			],
		]);
	}

}
