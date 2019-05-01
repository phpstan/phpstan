<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class MissingClosureNativeReturnTypehintRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new MissingClosureNativeReturnTypehintRule(true);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/missing-closure-native-return-typehint.php'], [
			[
				'Anonymous function should have native typehint "void".',
				10,
			],
			[
				'Anonymous function should have native typehint "void".',
				13,
			],
			[
				'Anonymous function should have native typehint "Generator".',
				16,
			],
			[
				'Mixing returning values with empty return statements - return null should be used here.',
				25,
			],
			[
				'Anonymous function should have native typehint "?int".',
				23,
			],
			[
				'Anonymous function should have native typehint "?int".',
				33,
			],
			[
				'Anonymous function sometimes return something but return statement at the end is missing.',
				40,
			],
		]);
	}

}
