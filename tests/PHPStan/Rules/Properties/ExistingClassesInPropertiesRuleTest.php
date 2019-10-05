<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassesInPropertiesRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassesInPropertiesRule(
			$broker,
			new ClassCaseSensitivityCheck($broker),
			true,
			false
		);
	}

	public function testNonexistentClass(): void
	{
		$this->analyse(
			[
				__DIR__ . '/data/properties-types.php',
			],
			[
				[
					'Property PropertiesTypes\Foo::$bar has unknown class PropertiesTypes\Bar as its type.',
					12,
				],
				[
					'Property PropertiesTypes\Foo::$bars has unknown class PropertiesTypes\Bar as its type.',
					18,
				],
				[
					'Property PropertiesTypes\Foo::$dolors has unknown class PropertiesTypes\Dolor as its type.',
					21,
				],
				[
					'Property PropertiesTypes\Foo::$dolors has unknown class PropertiesTypes\Ipsum as its type.',
					21,
				],
				[
					'Property PropertiesTypes\Foo::$fooWithWrongCase has unknown class PropertiesTypes\BAR as its type.',
					24,
				],
				[
					'Property PropertiesTypes\Foo::$fooWithWrongCase has unknown class PropertiesTypes\Fooo as its type.',
					24,
				],
				[
					'Class PropertiesTypes\Foo referenced with incorrect case: PropertiesTypes\FOO.',
					24,
				],
				[
					'Property PropertiesTypes\Foo::$withTrait has invalid type PropertiesTypes\SomeTrait.',
					27,
				],
				[
					'Property PropertiesTypes\Foo::$nonexistentClassInGenericObjectType has unknown class PropertiesTypes\Foooo as its type.',
					33,
				],
				[
					'Property PropertiesTypes\Foo::$nonexistentClassInGenericObjectType has unknown class PropertiesTypes\Barrrr as its type.',
					33,
				],
			]
		);
	}

	public function testNativeTypes(): void
	{
		if (PHP_VERSION_ID < 70400) {
			$this->markTestSkipped('Test requires PHP 7.4.');
		}

		$this->analyse([__DIR__ . '/data/properties-native-types.php'], [
			[
				'Property PropertiesNativeTypes\Foo::$bar has unknown class PropertiesNativeTypes\Bar as its type.',
				10,
			],
			[
				'Property PropertiesNativeTypes\Foo::$baz has unknown class PropertiesNativeTypes\Baz as its type.',
				13,
			],
			[
				'Property PropertiesNativeTypes\Foo::$baz has unknown class PropertiesNativeTypes\Baz as its type.',
				13,
			],
		]);
	}

}
