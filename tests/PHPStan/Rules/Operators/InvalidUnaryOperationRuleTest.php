<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

class InvalidUnaryOperationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidUnaryOperationRule();
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/invalid-unary.php'],
			[
				[
					'Unary operation "+" on string results in an error.',
					10,
				],
				[
					'Unary operation "-" on string results in an error.',
					11,
				],
				[
					'Unary operation "+" on \'bla\' results in an error.',
					16,
				],
				[
					'Unary operation "-" on \'bla\' results in an error.',
					17,
				],
			]
		);
	}

}
