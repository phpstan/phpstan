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
				'Cannot access property FooAccessStaticProperties::$foo from current scope.',
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
		]);
	}

}
