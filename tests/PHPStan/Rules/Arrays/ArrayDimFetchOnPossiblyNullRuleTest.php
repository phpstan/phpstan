<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class ArrayDimFetchOnPossiblyNullRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ArrayDimFetchOnPossiblyNullRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/arrayDimFetchNullable.php'], [
			[
				'Accessing offset \'test\' on possibly null value of type array|null.',
				5,
			],
			[
				'Accessing an offset on possibly null value of type array|null.',
				6,
			],
		]);
	}

}
