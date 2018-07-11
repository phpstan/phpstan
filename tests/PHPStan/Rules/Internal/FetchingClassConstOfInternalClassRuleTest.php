<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PHPStan\Rules\RuleLevelHelper;

class FetchingClassConstOfInternalClassRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($this->createBroker(), true, false, true);
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/fetching-class-const-of-internal-class-in-internal-path-definition.php',
		]);

		return new FetchingClassConstOfInternalClassRule($broker, $ruleLevelHelper, $internalScopeHelper);
	}

	public function testFetchingClassConstOfInternalClass(): void
	{
		require_once __DIR__ . '/data/fetching-class-const-of-internal-class-in-internal-path-definition.php';
		require_once __DIR__ . '/data/fetching-class-const-of-internal-class-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/fetching-class-const-of-internal-class.php'],
			[
				[
					'Fetching class constant class of internal class FetchingClassConstOfInternalClassInExternalPath\InternalFoo.',
					13,
				],
				[
					'Fetching internal class constant INTERNAL_FOO of class FetchingClassConstOfInternalClassInExternalPath\Foo.',
					15,
				],
				[
					'Fetching class constant class of internal class FetchingClassConstOfInternalClassInExternalPath\InternalFoo.',
					16,
				],
				[
					'Fetching class constant class of internal class FetchingClassConstOfInternalClassInExternalPath\InternalFoo.',
					17,
				],
			]
		);
	}

}
