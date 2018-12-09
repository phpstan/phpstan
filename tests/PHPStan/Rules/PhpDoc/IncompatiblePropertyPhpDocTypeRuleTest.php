<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Rule;

class IncompatiblePropertyPhpDocTypeRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		return new IncompatiblePropertyPhpDocTypeRule();
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/incompatible-property-phpdoc.php'],
			[
				[
					'PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$foo contains unresolvable type.',
					9,
				],
				[
					'PHPDoc tag @var for property InvalidPhpDoc\FooWithProperty::$bar contains unresolvable type.',
					12,
				],
			]
		);
	}

}
