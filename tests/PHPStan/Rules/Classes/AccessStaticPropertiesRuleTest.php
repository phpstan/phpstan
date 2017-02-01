<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class AccessStaticPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessStaticPropertiesRule(
			$this->createBroker()
		);
	}

	public function testAccessStaticProperties()
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
		]);
	}

}
