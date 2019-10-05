<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Generics\GenericObjectTypeCheck;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class InvalidPhpDocVarTagTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new InvalidPhpDocVarTagTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class),
			$broker,
			new ClassCaseSensitivityCheck($broker),
			new GenericObjectTypeCheck(),
			true
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-var-tag-type.php'], [
			[
				'PHPDoc tag @var for variable $test contains unresolvable type.',
				13,
			],
			[
				'PHPDoc tag @var contains unresolvable type.',
				16,
			],
			[
				'PHPDoc tag @var for variable $test contains unknown class InvalidVarTagType\aray.',
				20,
			],
			[
				'PHPDoc tag @var for variable $value contains unresolvable type.',
				22,
			],
			[
				'PHPDoc tag @var for variable $staticVar contains unresolvable type.',
				27,
			],
			[
				'Class InvalidVarTagType\Foo referenced with incorrect case: InvalidVarTagType\foo.',
				31,
			],
			[
				'PHPDoc tag @var for variable $test has invalid type InvalidVarTagType\FooTrait.',
				34,
			],
			[
				'PHPDoc tag @var for variable $test contains generic type InvalidPhpDoc\Foo<stdClass> but class InvalidPhpDoc\Foo is not generic.',
				40,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int> in PHPDoc tag @var for variable $test does not specify all template types of class InvalidPhpDocDefinitions\FooGeneric: T, U',
				46,
			],
			[
				'Generic type InvalidPhpDocDefinitions\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @var for variable $test specifies 3 template types, but class InvalidPhpDocDefinitions\FooGeneric supports only 2: T, U',
				49,
			],
			[
				'Type Throwable in generic type InvalidPhpDocDefinitions\FooGeneric<int, Throwable> in PHPDoc tag @var for variable $test is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				52,
			],
			[
				'Type stdClass in generic type InvalidPhpDocDefinitions\FooGeneric<int, stdClass> in PHPDoc tag @var for variable $test is not subtype of template type U of Exception of class InvalidPhpDocDefinitions\FooGeneric.',
				55,
			],
		]);
	}

}
