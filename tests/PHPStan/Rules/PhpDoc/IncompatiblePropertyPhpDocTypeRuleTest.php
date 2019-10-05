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
