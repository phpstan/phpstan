<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Type\FileTypeMapper;

class IncompatiblePhpDocTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new IncompatiblePhpDocTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/incompatible-types.php'], [
			[
				'PHPDoc tag @param references unknown parameter: $unknown',
				12,
			],
			[
				'PHPDoc tag @param for parameter $b with type array is incompatible with native type string.',
				12,
			],
			[
				'PHPDoc tag @param for parameter $d with type float|int is not subtype of native type int.',
				12,
			],
			[
				'PHPDoc tag @return with type string is incompatible with native type int.',
				66,
			],
			[
				'PHPDoc tag @return with type int|string is not subtype of native type int.',
				75,
			],
			[
				'PHPDoc tag @param for parameter $strings with type array<int> is incompatible with native type array<int, string>.',
				91,
			],
			[
				'PHPDoc tag @param for parameter $numbers with type array<int, string> is incompatible with native type array<int, int>.',
				99,
			],
			[
				'PHPDoc tag @param for parameter $arr contains unresolvable type.',
				117,
			],
			[
				'PHPDoc tag @param references unknown parameter: $arrX',
				117,
			],
			[
				'PHPDoc tag @return contains unresolvable type.',
				117,
			],
			[
				'PHPDoc tag @param for parameter $foo contains unresolvable type.',
				126,
			],
			[
				'PHPDoc tag @return contains unresolvable type.',
				126,
			],
		]);
	}

}
