<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;

class ClosureReturnTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ClosureReturnTypeRule(new FunctionReturnTypeCheck());
	}

	public function testClosureReturnTypeRule()
	{
		$this->analyse([__DIR__ . '/data/closureReturnTypes.php'], [
			[
				'Anonymous function should return int but returns string.',
				15,
			],
			[
				'Anonymous function should return int but empty return statement found.',
				16,
			],
			[
				'Anonymous function should return string but returns int.',
				21,
			],
			[
				'Anonymous function should return string but empty return statement found.',
				22,
			],
			[
				'Anonymous function should return ClosureReturnTypes\Foo but returns ClosureReturnTypes\Bar.',
				27,
			],
			[
				'Anonymous function should return SomeOtherNamespace\Foo but returns ClosureReturnTypes\Foo.',
				31,
			],
			[
				'Anonymous function should return SomeOtherNamespace\Baz but returns ClosureReturnTypes\Foo.',
				36,
			],
		]);
	}

}
