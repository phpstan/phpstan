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
				'Offset \'b\' does not exist on array(\'a\' => stdClass, 0 => 2).',
				17,
			],
			[
				'Offset 1 does not exist on array(\'a\' => stdClass, 0 => 2).',
				18,
			],
			[
				'Cannot access array offset on null.',
				35,
			],
			[
				'Offset \'a\' does not exist on array(\'b\' => 1).',
				55,
			],
		]);
	}

}
