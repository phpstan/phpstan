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
			self::getContainer()->getByType(FileTypeMapper::class),
			new GenericAncestorsCheck(
				$this->createBroker(),
				new GenericObjectTypeCheck()
			)
		);
	}

	public function testRuleExtends(): void
	{
		$this->analyse([__DIR__ . '/data/class-ancestors-extends.php'], [
			[
				'Class ClassAncestorsExtends\FooDoesNotExtendAnything has @extends tag, but does not extend any class.',
				26,
			],
			[
				'The @extends tag of class ClassAncestorsExtends\FooDuplicateExtendsTags describes ClassAncestorsExtends\FooGeneric2 but the class extends ClassAncestorsExtends\FooGeneric.',
				35,
			],
			[
				'The @extends tag of class ClassAncestorsExtends\FooWrongClassExtended describes ClassAncestorsExtends\FooGeneric2 but the class extends ClassAncestorsExtends\FooGeneric.',
				43,
			],
			[
				'Class ClassAncestorsExtends\FooWrongTypeInExtendsTag @extends tag contains incompatible type class-string<ClassAncestorsExtends\T>.',
				51,
			],
			[
				'Generic type ClassAncestorsExtends\FooGeneric<int> in PHPDoc tag @extends does not specify all template types of class ClassAncestorsExtends\FooGeneric: T, U',
				67,
			],
			[
				'Generic type ClassAncestorsExtends\FooGeneric<int, InvalidArgumentException, string> in PHPDoc tag @extends specifies 3 template types, but class ClassAncestorsExtends\FooGeneric supports only 2: T, U',
				75,
			],
			[
				'Type Throwable in generic type ClassAncestorsExtends\FooGeneric<int, Throwable> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestorsExtends\FooGeneric.',
				83,
			],
			[
				'Type stdClass in generic type ClassAncestorsExtends\FooGeneric<int, stdClass> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestorsExtends\FooGeneric.',
				91,
			],
			[
				'PHPDoc tag @extends has invalid type ClassAncestorsExtends\Zazzuuuu.',
				99,
			],
			[
				'Type mixed in generic type ClassAncestorsExtends\FooGeneric<int, mixed> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestorsExtends\FooGeneric.',
				108,
			],
			[
				'Type Throwable in generic type ClassAncestorsExtends\FooGeneric<int, Throwable> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestorsExtends\FooGeneric.',
				117,
			],
			[
				'Type stdClass in generic type ClassAncestorsExtends\FooGeneric<int, stdClass> in PHPDoc tag @extends is not subtype of template type U of Exception of class ClassAncestorsExtends\FooGeneric.',
				163,
			],
		]);
	}

}
