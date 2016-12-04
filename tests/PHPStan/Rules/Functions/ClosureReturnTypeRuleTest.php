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
		if (PHP_VERSION_ID >= 70100) {
			$this->markTestSkipped(
				'Test can be run only on PHP 7.0 - higher versions fail with the following test in the parse phase.'
			);
		}
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

	/**
	 * @requires PHP 7.1
	 */
	public function testClosureReturnTypeNullableTypehints()
	{
		$this->analyse([__DIR__ . '/data/closure-nullableReturnTypes.php'], [
			[
				'Anonymous function should return int but returns string.',
				6,
			],
		]);
	}

}
