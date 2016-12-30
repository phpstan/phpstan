<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionReturnTypeCheck;

class ClosureReturnTypeRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ClosureReturnTypeRule(new FunctionReturnTypeCheck(new \PhpParser\PrettyPrinter\Standard()));
	}

	public function testClosureReturnTypeRule()
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

	public function testClosureReturnTypeRulePhp70()
	{
		if (PHP_VERSION_ID >= 70100) {
			$this->markTestSkipped(
				'Test can be run only on PHP 7.0 - higher versions fail with the following test in the parse phase.'
			);
		}
		$this->analyse([__DIR__ . '/data/closureReturnTypes-7.0.php'], [
			[
				'Anonymous function should return int but empty return statement found.',
				4,
			],
			[
				'Anonymous function should return string but empty return statement found.',
				8,
			],
		]);
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testClosureReturnTypePhp71Typehints()
	{
		if (self::isObsoletePhpParserVersion()) {
			$this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
		}
		$this->analyse([__DIR__ . '/data/closure-7.1ReturnTypes.php'], [
			[
				'Anonymous function should return int|null but returns string.',
				6,
			],
			[
				'Anonymous function should return iterable(mixed[]) but returns string.',
				13,
			],
		]);
	}

}
