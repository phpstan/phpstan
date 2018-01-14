<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class IncompatiblePhpDocInheritedReturnTypeTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new IncompatiblePhpDocInheritedReturnType(
			$this->getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule()
	{
		$this->analyse([__DIR__ . '/data/incompatible-return-type.php'], [
			[
				'PHPDoc return type string does contain inherited return type int. Expected compound int|string.',
				32
			],
			[
				'PHPDoc return type int|string does contain inherited return type float. Expected compound float|int|string.',
				39
			],
			[
				'PHPDoc return type bool does contain inherited return type float|int|string. Expected compound bool|float|int|string.',
				56
			]
		]);
	}

}
