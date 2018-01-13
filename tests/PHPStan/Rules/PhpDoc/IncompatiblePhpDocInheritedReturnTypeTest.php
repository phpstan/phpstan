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
				'PHPDoc return type int does contain inherited return type string. Expected compound string|int.',
				32,
			],
			[
				'PHPDoc return type compound int|string does contain inherited return type float.',
				39,
			],
		]);
	}

}
