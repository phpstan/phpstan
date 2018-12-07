<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\RuleLevelHelper;

class TypesAssignedToPropertiesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new TypesAssignedToPropertiesRule(new RuleLevelHelper($this->createBroker(), true, false, true), new PropertyDescriptor(), new PropertyReflectionFinder());
	}

	public function testTypesAssignedToProperties(): void
	{
		$this->analyse(
			[__DIR__ . '/data/properties-assigned-types.php'],
			[
				[
					'Property PropertiesAssignedTypes\Foo::$stringProperty (string) does not accept int.',
					29,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$intProperty (int) does not accept string.',
					31,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$fooProperty (PropertiesAssignedTypes\Foo) does not accept PropertiesAssignedTypes\Bar.',
					33,
				],
				[
					'Static property PropertiesAssignedTypes\Foo::$staticStringProperty (string) does not accept int.',
					35,
				],
				[
					'Static property PropertiesAssignedTypes\Foo::$staticStringProperty (string) does not accept int.',
					37,
				],
				[
					'Static property PropertiesAssignedTypes\Ipsum::$parentStringProperty (string) does not accept int.',
					39,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$unionPropertySelf (array<PropertiesAssignedTypes\Foo>|(iterable<PropertiesAssignedTypes\Foo>&PropertiesAssignedTypes\Collection)) does not accept PropertiesAssignedTypes\Foo.',
					44,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$unionPropertySelf (array<PropertiesAssignedTypes\Foo>|(iterable<PropertiesAssignedTypes\Foo>&PropertiesAssignedTypes\Collection)) does not accept array<int, PropertiesAssignedTypes\Bar>.',
					45,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$unionPropertySelf (array<PropertiesAssignedTypes\Foo>|(iterable<PropertiesAssignedTypes\Foo>&PropertiesAssignedTypes\Collection)) does not accept PropertiesAssignedTypes\Bar.',
					46,
				],
				[
					'Property PropertiesAssignedTypes\Ipsum::$parentStringProperty (string) does not accept int.',
					48,
				],
				[
					'Static property PropertiesAssignedTypes\Ipsum::$parentStaticStringProperty (string) does not accept int.',
					50,
				],
				[
					'Property PropertiesAssignedTypes\Foo::$intProperty (int) does not accept string.',
					60,
				],
				[
					'Property PropertiesAssignedTypes\Ipsum::$foo (PropertiesAssignedTypes\Ipsum) does not accept PropertiesAssignedTypes\Bar.',
					143,
				],
				[
					'Static property PropertiesAssignedTypes\Ipsum::$fooStatic (PropertiesAssignedTypes\Ipsum) does not accept PropertiesAssignedTypes\Bar.',
					144,
				],
			]
		);
	}

}
