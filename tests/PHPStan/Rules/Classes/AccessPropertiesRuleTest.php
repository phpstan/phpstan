<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\RuleLevelHelper;

class AccessPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/** @var bool */
	private $checkThisOnly;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new AccessPropertiesRule($this->createBroker(), new RuleLevelHelper(), $this->checkThisOnly);
	}

	public function testAccessProperties()
	{
		$this->checkThisOnly = false;
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
					'Cannot access property $propertyOnString on string.',
					31,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$foo from current scope.',
					42,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$bar from current scope.',
					43,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$baz.',
					49,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$nonexistent.',
					52,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$foo from current scope.',
					58,
				],
				[
					'Cannot access property TestAccessProperties\FooAccessProperties::$bar from current scope.',
					59,
				],
				[
					'Access to property $foo on an unknown class TestAccessProperties\UnknownClass.',
					63,
				],
			]
		);
	}

	public function testAccessPropertiesOnThisOnly()
	{
		$this->checkThisOnly = true;
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
