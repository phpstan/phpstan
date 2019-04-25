<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\ClassCaseSensitivityCheck;

class ExistingClassInMethodExistsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new ExistingClassInMethodExistsRule(
			$broker,
			new ClassCaseSensitivityCheck($broker),
			true
		);
	}

	public function testExistingClassInMethodExists(): void
	{
		$this->analyse([__DIR__ . '/data/method-exists.php'], [
			[
				'Class UnknownClass not found.',
				8,
			],
			[
				'Class Foo referenced with incorrect case: foo.',
				9,
			],
		]);
	}

}
