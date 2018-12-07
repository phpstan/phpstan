<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

class InvalidPartOfEncapsedStringRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidPartOfEncapsedStringRule(new \PhpParser\PrettyPrinter\Standard());
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/invalid-encapsed-part.php'],
			[
				[
					'Part $std (stdClass) of encapsed string cannot be cast to string.',
					8,
				],
			]
		);
	}

}
