<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PHPStan\Analyser\TypeSpecifier;

class ImpossibleCheckTypeFunctionCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkAlwaysTrueCheckTypeFunctionCall;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$printer = new \PhpParser\PrettyPrinter\Standard();
		$typeSpecifier = new TypeSpecifier($printer);
		return new ImpossibleCheckTypeFunctionCallRule($typeSpecifier, $this->checkAlwaysTrueCheckTypeFunctionCall);
	}

	public function testImpossibleCheckTypeFunctionCall()
	{
		$this->checkAlwaysTrueCheckTypeFunctionCall = true;
		$this->analyse(
			[__DIR__ . '/data/check-type-function-call.php'],
			[
				[
					'Call to function is_int() will always evaluate to true.',
					21,
				],
				[
					'Call to function is_int() will always evaluate to false.',
					27,
				],
			]
		);
	}

	public function testImpossibleCheckTypeFunctionCallWithoutAlwaysTrue()
	{
		$this->checkAlwaysTrueCheckTypeFunctionCall = false;
		$this->analyse(
			[__DIR__ . '/data/check-type-function-call.php'],
			[
				[
					'Call to function is_int() will always evaluate to false.',
					27,
				],
			]
		);
	}

}
