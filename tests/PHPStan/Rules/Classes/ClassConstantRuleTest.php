<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\Rule;

class ClassConstantRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new ClassConstantRule($this->createBroker());
	}

	public function testClassConstant()
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
			]
		);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testClassConstantVisibility()
	{
		$this->analyse([__DIR__ . '/data/class-constant-visibility.php'], [
			[
				'Cannot access constant ClassConstantVisibility\Bar::PRIVATE_BAR from current scope.',
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
				'Cannot access constant ClassConstantVisibility\Foo::PRIVATE_FOO from current scope.',
				46,
			],
			[
				'Cannot access constant ClassConstantVisibility\Foo::PRIVATE_FOO from current scope.',
				47,
			],
			[
				'Cannot access constant ClassConstantVisibility\Foo::PROTECTED_FOO from current scope.',
				63,
			],
		]);
	}

}
