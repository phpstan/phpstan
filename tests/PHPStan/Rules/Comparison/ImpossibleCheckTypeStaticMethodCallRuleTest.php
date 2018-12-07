<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Tests\AssertionClassStaticMethodTypeSpecifyingExtension;
use PHPStan\Type\PHPUnit\Assert\AssertStaticMethodTypeSpecifyingExtension;

class ImpossibleCheckTypeStaticMethodCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	public function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleCheckTypeStaticMethodCallRule(
			new ImpossibleCheckTypeHelper(
				$this->createBroker(),
				$this->getTypeSpecifier()
			),
			true
		);
	}

	/**
	 * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
	 */
	protected function getStaticMethodTypeSpecifyingExtensions(): array
	{
		return [
			new AssertionClassStaticMethodTypeSpecifyingExtension(null),
			new AssertStaticMethodTypeSpecifyingExtension(),
		];
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/impossible-static-method-call.php'],
			[
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with int will always evaluate to true.',
					13,
				],
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with string will always evaluate to false.',
					14,
				],
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with int will always evaluate to true.',
					31,
				],
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with string will always evaluate to false.',
					32,
				],
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with 1 and 2 will always evaluate to true.',
					33,
				],
				[
					'Call to static method PHPStan\Tests\AssertionClass::assertInt() with arguments 1, 2 and 3 will always evaluate to true.',
					34,
				],
			]
		);
	}

}
