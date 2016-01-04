<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class AccessOwnPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessOwnPropertiesRule($this->getBroker());
	}

	public function testAccessOwnProperties()
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
			]
		);
	}

}
