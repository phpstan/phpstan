<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\RuleLevelHelper;

class AccessStaticPropertiesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new AccessStaticPropertiesRule(
			$broker,
			new RuleLevelHelper($broker, true, false, true),
			new ClassCaseSensitivityCheck($broker)
		);
	}

	public function testAccessStaticProperties(): void
	{
		$this->analyse([__DIR__ . '/data/access-static-properties.php'], [
			[
				'Access to an undefined static property FooAccessStaticProperties::$bar.',
				23,
			],
			[
				'Access to an undefined static property BarAccessStaticProperties::$bar.',
				24,
			],
			[
				'Access to an undefined static property FooAccessStaticProperties::$bar.',
				25,
			],
			[
				'Static access to instance property FooAccessStaticProperties::$loremIpsum.',
				26,
			],
			[
				'IpsumAccessStaticProperties::ipsum() accesses parent::$lorem but IpsumAccessStaticProperties does not extend any class.',
				42,
			],
			[
				'Access to protected property $foo of class FooAccessStaticProperties.',
				44,
			],
			[
				'Access to static property $test on an unknown class UnknownStaticProperties.',
				47,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$baz.',
				53,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$nonexistent.',
				55,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$emptyBaz.',
				63,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$emptyNonexistent.',
				65,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$anotherNonexistent.',
				71,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$anotherNonexistent.',
				72,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$anotherEmptyNonexistent.',
				75,
			],
			[
				'Access to an undefined static property IpsumAccessStaticProperties::$anotherEmptyNonexistent.',
				78,
			],
			[
				'Accessing self::$staticFooProperty outside of class scope.',
				84,
			],
			[
				'Accessing static::$staticFooProperty outside of class scope.',
				85,
			],
			[
				'Accessing parent::$staticFooProperty outside of class scope.',
				86,
			],
			[
				'Access to protected property $foo of class FooAccessStaticProperties.',
				89,
			],
			[
				'Static access to instance property FooAccessStaticProperties::$loremIpsum.',
				90,
			],
			[
				'Access to an undefined static property FooAccessStaticProperties::$nonexistent.',
				94,
			],
			[
				'Access to static property $test on an unknown class NonexistentClass.',
				97,
			],
			[
				'Access to an undefined static property FooAccessStaticProperties&SomeInterface::$nonexistent.',
				108,
			],
			[
				'Cannot access static property $foo on int|string.',
				113,
			],
			[
				'Class FooAccessStaticProperties referenced with incorrect case: FOOAccessStaticPropertieS.',
				119,
			],
			[
				'Access to an undefined static property FooAccessStaticProperties::$unknownProperties.',
				119,
			],
			[
				'Class FooAccessStaticProperties referenced with incorrect case: FOOAccessStaticPropertieS.',
				120,
			],
			[
				'Static access to instance property FooAccessStaticProperties::$loremIpsum.',
				120,
			],
			[
				'Class FooAccessStaticProperties referenced with incorrect case: FOOAccessStaticPropertieS.',
				121,
			],
			[
				'Access to protected property $foo of class FooAccessStaticProperties.',
				121,
			],
			[
				'Class FooAccessStaticProperties referenced with incorrect case: FOOAccessStaticPropertieS.',
				122,
			],
			[
				'Access to an undefined static property ClassOrString|string::$unknownProperty.',
				141,
			],
			[
				'Static access to instance property ClassOrString::$instanceProperty.',
				152,
			],
			[
				'Access to an undefined static property AccessPropertyWithDimFetch::$foo.',
				163,
			],
			[
				'Access to an undefined static property AccessInIsset::$foo.',
				185,
			],
		]);
	}

}
