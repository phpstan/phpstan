<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Type\FileTypeMapper;

class InvalidThrowsPhpDocValueRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidThrowsPhpDocValueRule(
			$this->getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/incompatible-throws.php'], [
			[
				'PHPDoc tag @throws with type Undefined is not subtype of Throwable',
				54,
			],
			[
				'PHPDoc tag @throws with type bool is not subtype of Throwable',
				61,
			],
			[
				'PHPDoc tag @throws with type DateTimeImmutable is not subtype of Throwable',
				68,
			],
			[
				'PHPDoc tag @throws with type DateTimeImmutable|Throwable is not subtype of Throwable',
				75,
			],
			[
				'PHPDoc tag @throws with type DateTimeImmutable&IteratorAggregate is not subtype of Throwable',
				82,
			],
		]);
	}

}
