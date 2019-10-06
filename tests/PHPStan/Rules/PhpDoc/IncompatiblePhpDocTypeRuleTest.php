<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Generics\GenericObjectTypeCheck;
use PHPStan\Type\FileTypeMapper;

class IncompatiblePhpDocTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IncompatiblePhpDocTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class),
			new GenericObjectTypeCheck()
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/incompatible-types.php'], [
			[
				'PHPDoc tag @param references unknown parameter: $unknown',
				12,
			],
			[
				'PHPDoc tag @param for parameter $b with type array is incompatible with native type string.',
				12,
			],
			[
				'PHPDoc tag @param for parameter $d with type float|int is not subtype of native type int.',
				12,
			],
			[
				'PHPDoc tag @return with type string is incompatible with native type int.',
				66,
			],
			[
				'PHPDoc tag @return with type int|string is not subtype of native type int.',
				75,
			],
			[
				'PHPDoc tag @param for parameter $strings with type array<int> is incompatible with native type array<int, string>.',
				91,
			],
			[
				'PHPDoc tag @param for parameter $numbers with type array<int, string> is incompatible with native type array<int, int>.',
				99,
			],
			[
				'PHPDoc tag @param for parameter $arr contains unresolvable type.',
				117,
			],
			[
				'PHPDoc tag @param references unknown parameter: $arrX',
				117,
			],
			[
				'PHPDoc tag @return contains unresolvable type.',
				117,
			],
			[
				'PHPDoc tag @param for parameter $foo contains unresolvable type.',
				126,
			],
			[
				'PHPDoc tag @return contains unresolvable type.',
				126,
			],
			[
				'PHPDoc tag @param for parameter $a with type T is not subtype of native type int.',
				154,
			],
			[
				'PHPDoc tag @param for parameter $b with type U of DateTimeInterface is not subtype of native type DateTime.',
				154,
			],
			[
				'PHPDoc tag @return with type DateTimeInterface is not subtype of native type DateTime.',
				154,
			],
			[
				'PHPDoc tag @param for parameter $foo contains generic type InvalidPhpDocDefinitions\Foo<stdClass> but class InvalidPhpDocDefinitions\Foo is not generic.',
				185,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int> in PHPDoc tag @param for parameter $baz does not specify all template types of class InvalidPhpDocDefinitions\FooGeneric: T, U',
				185,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @param for parameter $lorem specifies 3 template types, but class InvalidPhpDocDefinitions\FooGeneric supports only 2: T, U',
				185,
			],
			[
				'Type Throwable in generic type InvalidPhpDocDefinitions\FooGeneric<int, Throwable> in PHPDoc tag @param for parameter $ipsum is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				185,
			],
			[
				'Type stdClass in generic type InvalidPhpDocDefinitions\FooGeneric<int, stdClass> in PHPDoc tag @param for parameter $dolor is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				185,
			],
			[
				'PHPDoc tag @return contains generic type InvalidPhpDocDefinitions\Foo<stdClass> but class InvalidPhpDocDefinitions\Foo is not generic.',
				185,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int> in PHPDoc tag @return does not specify all template types of class InvalidPhpDocDefinitions\FooGeneric: T, U',
				201,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @return specifies 3 template types, but class InvalidPhpDocDefinitions\FooGeneric supports only 2: T, U',
				209,
			],
			[
				'Type Throwable in generic type InvalidPhpDocDefinitions\FooGeneric<int, Throwable> in PHPDoc tag @return is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				217,
			],
			[
				'Type stdClass in generic type InvalidPhpDocDefinitions\FooGeneric<int, stdClass> in PHPDoc tag @return is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				225,
			],
			[
				'Type mixed in generic type InvalidPhpDocDefinitions\FooGeneric<int, mixed> in PHPDoc tag @param for parameter $t is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				242,
			],
			[
				'Type Throwable in generic type InvalidPhpDocDefinitions\FooGeneric<int, Throwable> in PHPDoc tag @param for parameter $v is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				242,
			],
			[
				'Type stdClass in generic type InvalidPhpDocDefinitions\FooGeneric<int, stdClass> in PHPDoc tag @param for parameter $x is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				242,
			],
		]);
	}

}
