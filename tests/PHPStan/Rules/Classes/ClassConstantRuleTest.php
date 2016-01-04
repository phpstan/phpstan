<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\Rule;

class ClassConstantRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): Rule
	{
		return new ClassConstantRule($this->getBroker());
	}

	public function testClassDoesNotExist()
	{
		$this->analyse(
			[
				__DIR__ . '/data/class-constant.php',
				__DIR__ . '/data/class-constant-defined.php',
			],
			[
				[
					'Class ClassConstantNamespace\Bar does not exist.',
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
			]
		);
	}

}
