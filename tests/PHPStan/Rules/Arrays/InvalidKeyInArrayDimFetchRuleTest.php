<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class InvalidKeyInArrayDimFetchRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidKeyInArrayDimFetchRule(true);
	}

	public function testInvalidKey(): void
	{
		$this->analyse(
			[__DIR__ . '/data/invalid-key-array-dim-fetch.php'],
			[
				[
					'Invalid array key type DateTimeImmutable.',
					7,
				],
				[
					'Invalid array key type array.',
					8,
				],
				[
					'Possibly invalid array key type stdClass|string.',
					24,
				],
				[
					'Invalid array key type DateTimeImmutable.',
					31,
				],
			]
		);
	}

}
