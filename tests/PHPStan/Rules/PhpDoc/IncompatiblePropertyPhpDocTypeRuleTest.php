<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Generics\GenericObjectTypeCheck;
use PHPStan\Rules\Rule;

class IncompatiblePropertyPhpDocTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		return new IncompatiblePropertyPhpDocTypeRule(new GenericObjectTypeCheck());
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/incompatible-property-phpdoc.php'], [
			[
				'PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$bar contains unresolvable type.',
				12,
			],
			[
				'PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$classStringInt contains unresolvable type.',
				18,
			],
			[
				'PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$fooGeneric contains generic type InvalidPhpDocDefinitions\Foo<stdClass> but class InvalidPhpDocDefinitions\Foo is not generic.',
				24,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int> in PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$notEnoughTypesGenericfoo does not specify all template types of class InvalidPhpDocDefinitions\FooGeneric: T, U',
				30,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$tooManyTypesGenericfoo specifies 3 template types, but class InvalidPhpDocDefinitions\FooGeneric supports only 2: T, U',
				33,
			],
			[
				'Type Throwable in generic type InvalidPhpDocDefinitions\FooGeneric<int, Throwable> in PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$invalidTypeGenericfoo is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				36,
			],
			[
				'Type stdClass in generic type InvalidPhpDocDefinitions\FooGeneric<int, stdClass> in PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$anotherInvalidTypeGenericfoo is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				39,
			],
		]);
	}

	public function testNativeTypes(): void
	{
		if (PHP_VERSION_ID < 70400) {
			$this->markTestSkipped('Test requires PHP 7.4.');
		}
		$this->analyse([__DIR__ . '/data/incompatible-property-native-types.php'], [
			[
				'PHPDoc tag @var for property IncompatiblePhpDocPropertyNativeType\Foo::$selfTwo with type object is not subtype of native type IncompatiblePhpDocPropertyNativeType\Foo.',
				12,
			],
			[
				'PHPDoc tag @var for property IncompatiblePhpDocPropertyNativeType\Foo::$foo with type IncompatiblePhpDocPropertyNativeType\Bar is incompatible with native type IncompatiblePhpDocPropertyNativeType\Foo.',
				15,
			],
			[
				'PHPDoc tag @var for property IncompatiblePhpDocPropertyNativeType\Foo::$stringOrInt with type int|string is not subtype of native type string.',
				21,
			],
		]);
	}

}
