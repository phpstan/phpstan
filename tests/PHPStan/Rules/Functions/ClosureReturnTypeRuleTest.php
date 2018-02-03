<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Rules\RuleLevelHelper;

class ClosureReturnTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ClosureReturnTypeRule(new FunctionReturnTypeCheck(new \PhpParser\PrettyPrinter\Standard(), new RuleLevelHelper($this->createBroker(), true, false, true)));
	}

	public function testClosureReturnTypeRule(): void
	{
		$this->analyse([__DIR__ . '/data/closureReturnTypes.php'], [
			[
				'Anonymous function should return int but returns string.',
				15,
			],
			[
				'Anonymous function should return string but returns int.',
				20,
			],
			[
				'Anonymous function should return ClosureReturnTypes\Foo but returns ClosureReturnTypes\Bar.',
				25,
			],
			[
				'Anonymous function should return SomeOtherNamespace\Foo but returns ClosureReturnTypes\Foo.',
				29,
			],
			[
				'Anonymous function should return SomeOtherNamespace\Baz but returns ClosureReturnTypes\Foo.',
				34,
			],
		]);
	}

	public function testClosureReturnTypePhp71Typehints(): void
	{
		$this->analyse([__DIR__ . '/data/closure-7.1ReturnTypes.php'], [
			[
				'Anonymous function should return int|null but returns string.',
				6,
			],
			[
				'Anonymous function should return iterable but returns string.',
				13,
			],
		]);
	}

}
