<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class NonexistentOffsetInArrayDimFetchRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new NonexistentOffsetInArrayDimFetchRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/nonexistent-offset.php'], [
			[
				'Offset string does not exist on array<int(0)|string, int(2)|stdClass>.',
				17,
			],
			[
				'Offset int(1) does not exist on array<int(0)|string, int(2)|stdClass>.',
				18,
			],
			[
				'Cannot access array offset on null.',
				35,
			],
			[
				'Offset string does not exist on array<string, int(1)>.',
				55,
			],
		]);
	}

}
