<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

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
				'Type parameter T of function a() has invalid bound float|int (only class name bounds are supported currently).',
				9,
			],
			[
				'Type parameter U of function b() has invalid bound DateTime|DateTimeImmutable (only class name bounds are supported currently).',
				18,
			],
			[
				'Type parameter T of function c() has unknown class TemplateTypeBoundFunctions\NonexistentClass as its bound.',
				25,
			],
			[
				'Class TemplateTypeBoundFunctions\Foo referenced with incorrect case: TemplateTypeBoundFunctions\foo.',
				33,
			],
		]);
	}

}
