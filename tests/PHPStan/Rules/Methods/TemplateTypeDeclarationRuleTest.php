<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\TemplateTypeCheck;
use PHPStan\Type\FileTypeMapper;

class TemplateTypeDeclarationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$fileTypeMapper = self::getContainer()->getByType(FileTypeMapper::class);

		$broker = $this->createBroker();
		return new TemplateTypeDeclarationRule(
			new TemplateTypeCheck($fileTypeMapper, $broker, new ClassCaseSensitivityCheck($broker), true)
		);
	}

	public function testRule(): void
	{
		require_once __DIR__ . '/data/template-type-bounds.php';
		$this->analyse([__DIR__ . '/data/template-type-bounds.php'], [
			[
				'Type parameter T of method TemplateTypeBoundMethods\Foo::a() has invalid bound float|int (only class name bounds are supported currently).',
				11,
			],
			[
				'Type parameter U of method TemplateTypeBoundMethods\Foo::b() has invalid bound DateTime|DateTimeImmutable (only class name bounds are supported currently).',
				20,
			],
			[
				'Type parameter T of method TemplateTypeBoundMethods\Foo::c() has unknown class TemplateTypeBoundMethods\NonexistentClass as its bound.',
				27,
			],
			[
				'Class TemplateTypeBoundMethods\Foo referenced with incorrect case: TemplateTypeBoundMethods\foo.',
				35,
			],
		]);
	}

}
