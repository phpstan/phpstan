<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class InvalidPhpDocVarTagTypeRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new InvalidPhpDocVarTagTypeRule(
			self::getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/invalid-var-tag-type.php'], [
			[
				'PHPDoc tag @var for variable $test contains unresolvable type.',
				13,
			],
			[
				'PHPDoc tag @var contains unresolvable type.',
				16,
			],
			[
				'PHPDoc tag @var for variable $value contains unresolvable type.',
				22,
			],
			[
				'PHPDoc tag @var for variable $staticVar contains unresolvable type.',
				27,
			],
		]);
	}

}
