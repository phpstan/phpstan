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
				'Offset string does not exist on array(\'a\' => stdClass, 0 => int(2)).',
				17,
			],
			[
				'Offset int(1) does not exist on array(\'a\' => stdClass, 0 => int(2)).',
				18,
			],
			[
				'Cannot access array offset on null.',
				35,
			],
			[
				'Offset string does not exist on array(\'b\' => int(1)).',
				55,
			],
		]);
	}

}
