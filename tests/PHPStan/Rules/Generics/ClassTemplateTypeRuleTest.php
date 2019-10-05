<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class ClassTemplateTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ClassTemplateTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class),
			new TemplateTypeCheck($this->createBroker())
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/class-template.php'], [
			[
				'PHPDoc tag @template for class ClassTemplateType\Foo cannot have existing class stdClass as its name.',
				8,
			],
			[
				'PHPDoc tag @template T for class ClassTemplateType\Bar has invalid bound type ClassTemplateType\Zazzzu.',
				16,
			],
			[
				'PHPDoc tag @template T for class ClassTemplateType\Baz with bound type int is not supported.',
				24,
			],
		]);
	}

}
