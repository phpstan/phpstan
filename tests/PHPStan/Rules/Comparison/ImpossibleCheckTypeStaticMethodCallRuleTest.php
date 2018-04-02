<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Tests\AssertionClassStaticMethodTypeSpecifyingExtension;

class ImpossibleCheckTypeStaticMethodCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	public function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleCheckTypeStaticMethodCallRule(true);
	}

	/**
	 * @return \PHPStan\Type\StaticMethodTypeSpecifyingExtension[]
	 */
	protected function getStaticMethodTypeSpecifyingExtensions(): array
	{
		return [
			new AssertionClassStaticMethodTypeSpecifyingExtension(null),
		];
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/impossible-static-method-call.php'], [
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
				'Call to static method PHPStan\Tests\AssertionClass::assertInt() with int(1) and int(2) will always evaluate to true.',
				33,
			],
			[
				'Call to static method PHPStan\Tests\AssertionClass::assertInt() with arguments int(1), int(2) and int(3) will always evaluate to true.',
				34,
			],
		]);
	}

}
