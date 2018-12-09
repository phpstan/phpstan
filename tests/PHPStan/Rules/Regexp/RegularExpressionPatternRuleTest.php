<?php declare(strict_types = 1);

namespace PHPStan\Rules\Regexp;

class RegularExpressionPatternRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new RegularExpressionPatternRule();
	}

	public function testValidRegexPatternBefore73(): void
	{
		if (\PHP_VERSION_ID >= 70300) {
			$this->markTestSkipped('This test requires PHP < 7.3.0');
		}

		$this->analyse(
			[__DIR__ . '/data/valid-regex-pattern.php'],
			[
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					6,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					7,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					11,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					12,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					16,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					17,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					21,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					22,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					26,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					27,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					29,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					29,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					32,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					33,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					35,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					35,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					38,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					39,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					41,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					41,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					43,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing ) at offset 1 in pattern: ~(~',
					43,
				],
			]
		);
	}

	public function testValidRegexPatternAfter73(): void
	{
		if (\PHP_VERSION_ID < 70300) {
			$this->markTestSkipped('This test requires PHP >= 7.3.0');
		}

		$this->analyse(
			[__DIR__ . '/data/valid-regex-pattern.php'],
			[
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					6,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					7,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					11,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					12,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					16,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					17,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					21,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					22,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					26,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					27,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					29,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					29,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					32,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					33,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					35,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					35,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					38,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					39,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					41,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					41,
				],
				[
					'Regex pattern is invalid: Delimiter must not be alphanumeric or backslash in pattern: nok',
					43,
				],
				[
					'Regex pattern is invalid: Compilation failed: missing closing parenthesis at offset 1 in pattern: ~(~',
					43,
				],
			]
		);
	}

}
