<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class ClassConstantRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new ClassConstantRule($broker, new RuleLevelHelper($broker, true, false, true), new ClassCaseSensitivityCheck($broker));
	}

	public function testClassConstant(): void
	{
		$this->analyse(
			[
				__DIR__ . '/data/class-constant.php',
				__DIR__ . '/data/class-constant-defined.php',
			],
			[
				[
					'Class ClassConstantNamespace\Bar not found.',
					6,
				],
				[
					'Using self outside of class scope.',
					7,
				],
				[
					'Access to undefined constant ClassConstantNamespace\Foo::DOLOR.',
					10,
				],
				[
					'Access to undefined constant ClassConstantNamespace\Foo::DOLOR.',
					16,
				],
				[
					'Using static outside of class scope.',
					18,
				],
				[
					'Using parent outside of class scope.',
					19,
				],
				[
					'Access to constant FOO on an unknown class ClassConstantNamespace\UnknownClass.',
					21,
				],
				[
					'Class ClassConstantNamespace\Foo referenced with incorrect case: ClassConstantNamespace\FOO.',
					26,
				],
				[
					'Class ClassConstantNamespace\Foo referenced with incorrect case: ClassConstantNamespace\FOO.',
					27,
				],
				[
					'Access to undefined constant ClassConstantNamespace\Foo::DOLOR.',
					27,
				],
				[
					'Class ClassConstantNamespace\Foo referenced with incorrect case: ClassConstantNamespace\FOO.',
					28,
				],
				[
					'Access to undefined constant ClassConstantNamespace\Foo|string::DOLOR.',
					33,
				],
			]
		);
	}

	public function testClassConstantVisibility(): void
	{
		if (PHP_VERSION_ID >= 70400) {
			$this->markTestSkipped('Test does not run on PHP 7.4 because of referencing parent:: without parent class.');
		}
		$this->analyse([__DIR__ . '/data/class-constant-visibility.php'], [
			[
				'Access to private constant PRIVATE_BAR of class ClassConstantVisibility\Bar.',
				25,
			],
			[
				'Access to parent::BAZ but ClassConstantVisibility\Foo does not extend any class.',
				27,
			],
			[
				'Access to undefined constant ClassConstantVisibility\Bar::PRIVATE_FOO.',
				45,
			],
			[
				'Access to private constant PRIVATE_FOO of class ClassConstantVisibility\Foo.',
				46,
			],
			[
				'Access to private constant PRIVATE_FOO of class ClassConstantVisibility\Foo.',
				47,
			],
			[
				'Access to undefined constant ClassConstantVisibility\Bar::PRIVATE_FOO.',
				60,
			],
			[
				'Access to protected constant PROTECTED_FOO of class ClassConstantVisibility\Foo.',
				71,
			],
			[
				'Access to undefined constant ClassConstantVisibility\WithFooAndBarConstant&ClassConstantVisibility\WithFooConstant::BAZ.',
				106,
			],
			[
				'Access to undefined constant ClassConstantVisibility\WithFooAndBarConstant|ClassConstantVisibility\WithFooConstant::BAR.',
				110,
			],
			[
				'Access to constant FOO on an unknown class ClassConstantVisibility\UnknownClassFirst.',
				112,
			],
			[
				'Access to constant FOO on an unknown class ClassConstantVisibility\UnknownClassSecond.',
				112,
			],
			[
				'Cannot access constant FOO on int|string.',
				116,
			],
			[
				'Class ClassConstantVisibility\Foo referenced with incorrect case: ClassConstantVisibility\FOO.',
				122,
			],
			[
				'Access to private constant PRIVATE_FOO of class ClassConstantVisibility\Foo.',
				122,
			],
		]);
	}

}
