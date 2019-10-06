<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class ClassAncestorsRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ClassAncestorsRule(
			$this->createBroker(),
			self::getContainer()->getByType(FileTypeMapper::class),
			new GenericObjectTypeCheck()
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/class-ancestors.php'], [
			[
				'Class ClassAncestors\FooDoesNotExtendAnything has @extends tag, but does not extend any class.',
				26,
			],
			[
				'Class ClassAncestors\FooDuplicateExtendsTags has multiple @extends tags, but can extend only one class.',
				35,
			],
			[
				'Class ClassAncestors\FooWrongClassExtended extends ClassAncestors\FooGeneric but the @extends tag describes ClassAncestors\FooGeneric2.',
				43,
			],
			[
				'Class ClassAncestors\FooWrongTypeInExtendsTag @extends tag contains incompatible type class-string<ClassAncestors\T>.',
				51,
			],
			[
				'Generic type ClassAncestors\FooGeneric<int> in PHPDoc tag @extends does not specify all template types of class ClassAncestors\FooGeneric: T, U',
				67,
			],
			[
				'Generic type ClassAncestors\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @extends specifies 3 template types, but class ClassAncestors\FooGeneric supports only 2: T, U',
				75,
			],
			[
				'Type Throwable in generic type ClassAncestors\FooGeneric<int, Throwable> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestors\FooGeneric.',
				83,
			],
			[
				'Type stdClass in generic type ClassAncestors\FooGeneric<int, stdClass> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestors\FooGeneric.',
				91,
			],
			[
				'PHPDoc tag @extends has invalid type ClassAncestors\Zazzuuuu.',
				99,
			],
			[
				'Type mixed in generic type ClassAncestors\FooGeneric<int, mixed> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestors\FooGeneric.',
				108,
			],
			[
				'Type Throwable in generic type ClassAncestors\FooGeneric<int, Throwable> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestors\FooGeneric.',
				117,
			],
			[
				'Type stdClass in generic type ClassAncestors\FooGeneric<int, stdClass> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestors\FooGeneric.',
				163,
			],
		]);
	}

}
