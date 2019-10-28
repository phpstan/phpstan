<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\MissingTypehintCheck;

class MissingFunctionParameterTypehintRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new MissingFunctionParameterTypehintRule($this->createBroker([], []), new MissingTypehintCheck());
	}

	public function testRule(): void
	{
		require_once __DIR__ . '/data/missing-function-parameter-typehint.php';
		$this->analyse([__DIR__ . '/data/missing-function-parameter-typehint.php'], [
			[
				'Function globalFunction() has parameter $b with no typehint specified.',
				9,
			],
			[
				'Function globalFunction() has parameter $c with no typehint specified.',
				9,
			],
			[
				'Function MissingFunctionParameterTypehint\namespacedFunction() has parameter $d with no typehint specified.',
				24,
			],
			[
				'Function MissingFunctionParameterTypehint\missingIterableTypehint() has parameter $a with no value type specified in iterable type array.',
				36,
			],
			[
				'Function MissingFunctionParameterTypehint\missingPhpDocIterableTypehint() has parameter $a with no value type specified in iterable type array.',
				44,
			],
			[
				'Function MissingFunctionParameterTypehint\unionTypeWithUnknownArrayValueTypehint() has parameter $a with no value type specified in iterable type array.',
				60,
			],
		]);
	}

}
