<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

class InheritanceOfInternalClassRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$internalScopeHelper = new InternalScopeHelper([
			__DIR__ . '/data/inheritance-of-internal-class-in-internal-path-definition.php',
		]);

		return new InheritanceOfInternalClassRule($broker, $internalScopeHelper);
	}

	public function testInheritanceOfInternalClassInClasses(): void
	{
		require_once __DIR__ . '/data/inheritance-of-internal-class-in-internal-path-definition.php';
		require_once __DIR__ . '/data/inheritance-of-internal-class-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/inheritance-of-internal-class-in-classes.php'],
			[
				[
					'Class InheritanceOfInternalClass\Bar4 extends internal class InheritanceOfInternalClassInExternalPath\InternalFoo.',
					20,
				],
			]
		);
	}

	public function testInheritanceOfInternalClassInAnonymousClasses(): void
	{
		require_once __DIR__ . '/data/inheritance-of-internal-class-in-internal-path-definition.php';
		require_once __DIR__ . '/data/inheritance-of-internal-class-in-external-path-definition.php';
		$this->analyse(
			[__DIR__ . '/data/inheritance-of-internal-class-in-anonymous-classes.php'],
			[
				[
					'Anonymous class extends internal class InheritanceOfInternalClassInExternalPath\InternalFoo.',
					17,
				],
			]
		);
	}

}
