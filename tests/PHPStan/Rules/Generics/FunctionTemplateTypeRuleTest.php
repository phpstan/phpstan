<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class FunctionTemplateTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new FunctionTemplateTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class),
			new TemplateTypeCheck($this->createBroker())
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/function-template.php'], [
			[
				'PHPDoc tag @template for function FunctionTemplateType\foo() cannot have existing class stdClass as its name.',
				8,
			],
			[
				'PHPDoc tag @template T for function FunctionTemplateType\bar() has invalid bound type FunctionTemplateType\Zazzzu.',
				16,
			],
		]);
	}

}
