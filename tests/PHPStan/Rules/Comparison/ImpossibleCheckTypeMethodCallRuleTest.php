<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Tests\AssertionClassMethodTypeSpecifyingExtension;

class ImpossibleCheckTypeMethodCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	public function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleCheckTypeMethodCallRule(true);
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new AssertionClassMethodTypeSpecifyingExtension(null),
		];
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/impossible-method-call.php'], [
			[
				'Call to method PHPStan\Tests\AssertionClass::assertString() will always evaluate to true.',
				14,
			],
			[
				'Call to method PHPStan\Tests\AssertionClass::assertString() will always evaluate to false.',
				15,
			],
		]);
	}

}
