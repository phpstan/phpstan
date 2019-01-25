<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

class DuplicateKeysInLiteralArraysRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new DuplicateKeysInLiteralArraysRule(
			new \PhpParser\PrettyPrinter\Standard()
		);
	}

	public function testDuplicateKeys(): void
	{
		define('PHPSTAN_DUPLICATE_KEY', 0);
		$this->analyse([__DIR__ . '/data/duplicate-keys.php'], [
			[
				'Array has 2 duplicate keys with value \'\' (null, NULL).',
				16,
			],
			[
				'Array has 4 duplicate keys with value 1 (1, 1, 1.0, true).',
				18,
			],
			[
				'Array has 3 duplicate keys with value 0 (false, 0, PHPSTAN_DUPLICATE_KEY).',
				24,
			],
			[
				'Array has 2 duplicate keys with value \'=\' (self::EQ, self::IS).',
				35,
			],
		]);
	}

}
