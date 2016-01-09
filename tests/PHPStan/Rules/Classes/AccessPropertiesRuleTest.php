<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class AccessPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessPropertiesRule($this->createBroker());
	}

	public function testAccessProperties()
	{
		$this->analyse(
			[__DIR__ . '/data/access-properties.php'],
			[
				[
					'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
					23,
				],
				[
					'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
					24,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$foo from current scope.',
					39,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$bar from current scope.',
					40,
				],
			]
		);
	}

}
