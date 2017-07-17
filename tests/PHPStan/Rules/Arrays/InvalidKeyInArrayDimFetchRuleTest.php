<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class InvalidKeyInArrayDimFetchRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidKeyInArrayDimFetchRule();
	}

	public function testInvalidKey()
	{
		$this->analyse([__DIR__ . '/data/invalid-key-array-dim-fetch.php'], [
			[
				'Invalid array key type DateTimeImmutable.',
				7,
			],
			[
				'Invalid array key type mixed[].',
				8,
			],
		]);
	}

}
