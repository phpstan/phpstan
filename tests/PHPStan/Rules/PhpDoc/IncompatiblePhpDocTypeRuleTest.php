<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Type\FileTypeMapper;

class IncompatiblePhpDocTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IncompatiblePhpDocTypeRule(
			$this->getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule()
	{
		$this->analyse([__DIR__ . '/data/incompatible-types.php'], [
			[
				'PHPDoc tag @param references unknown parameter $unknown',
				12,
			],
			[
				'PHPDoc tag @param for parameter $b with type mixed[] is incompatible with native type string',
				12,
			],
			[
				'PHPDoc tag @param for parameter $d with type float|int is not subtype of native type int',
				12,
			],
			[
				'PHPDoc tag @param for parameter $strings with type string[][] is incompatible with native type string[]',
				30,
			],
			[
				'PHPDoc tag @return with type string is incompatible with native type int',
				66,
			],
			[
				'PHPDoc tag @return with type int|string is not subtype of native type int',
				75,
			],
		]);
	}

}
