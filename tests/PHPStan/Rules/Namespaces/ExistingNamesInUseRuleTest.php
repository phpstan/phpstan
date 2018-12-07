<?php declare(strict_types = 1);

namespace PHPStan\Rules\Namespaces;

use PHPStan\Rules\ClassCaseSensitivityCheck;

class ExistingNamesInUseRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new ExistingNamesInUseRule($broker, new ClassCaseSensitivityCheck($broker), true);
	}

	public function testRule(): void
	{
		require_once __DIR__ . '/data/uses-defined.php';
		$this->analyse(
			[__DIR__ . '/data/uses.php'],
			[
				[
					'Used function Uses\bar not found.',
					7,
				],
				[
					'Used constant Uses\OTHER_CONSTANT not found.',
					8,
				],
				[
					'Function Uses\foo used with incorrect case: Uses\Foo.',
					9,
				],
				[
					'Interface Uses\Lorem referenced with incorrect case: Uses\LOREM.',
					10,
				],
			]
		);
	}

}
