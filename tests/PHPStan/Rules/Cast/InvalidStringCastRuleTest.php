<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

class InvalidStringCastRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidStringCastRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-string-cast.php'], [
			[
				'Cannot cast stdClass to string.',
				7,
			],
		]);
	}

}
