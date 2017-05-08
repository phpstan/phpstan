<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\RuleLevelHelper;

class CallMethodsOnPossiblyNullRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallMethodsOnPossiblyNullRule(new RuleLevelHelper(true), false);
	}

	public function testExistingClassInTypehint()
	{
		$this->analyse([__DIR__ . '/data/possibly-nullable.php'], [
			[
				'Calling method format() on possibly nullable type DateTimeImmutable|null.',
				11,
			],
		]);
	}

}
