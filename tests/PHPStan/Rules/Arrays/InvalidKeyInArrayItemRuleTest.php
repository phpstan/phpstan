<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class InvalidKeyInArrayItemRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidKeyInArrayItemRule();
	}

	public function testInvalidKey()
	{
		$this->analyse([__DIR__ . '/data/invalid-key-array-item.php'], [
			[
				'Invalid array key type DateTimeImmutable.',
				10,
			],
			[
				'Invalid array key type mixed[].',
				11,
			],
		]);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testInvalidKeyInList()
	{
		$this->analyse([__DIR__ . '/data/invalid-key-list.php'], [
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
