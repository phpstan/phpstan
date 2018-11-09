<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class OverwrittenVariableInForeachRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new OverwrittenVariableInForeachRule();
	}

	public function test(): void
	{
		$this->analyse([__DIR__ . '/data/overwritten-variable-in-foreach.php'], [
			[
				'foreach will overwrite variable $value.',
				12,
			],
			[
				'foreach will overwrite variable $key.',
				22,
			],
			[
				'foreach will overwrite variable $value.',
				33,
			],
			[
				'foreach will overwrite variable $key.',
				33,
			],
		]);
	}

}
