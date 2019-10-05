<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class TraitTemplateTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TraitTemplateTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class),
			new TemplateTypeCheck($this->createBroker())
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/trait-template.php'], [
			[
				'PHPDoc tag @template for trait TraitTemplateType\Foo cannot have existing class stdClass as its name.',
				8,
			],
			[
				'PHPDoc tag @template T for trait TraitTemplateType\Bar has invalid bound type TraitTemplateType\Zazzzu.',
				16,
			],
			[
				'PHPDoc tag @template T for trait TraitTemplateType\Baz with bound type int is not supported.',
				24,
			],
		]);
	}

}
