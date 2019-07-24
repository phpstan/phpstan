<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

class WrongVariableNameInVarTagRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new WrongVariableNameInVarTagRule(
			self::getContainer()->getByType(FileTypeMapper::class)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/wrong-variable-name-var.php'], [
			[
				'Variable $foo in PHPDoc tag @var does not match assigned variable $test.',
				17,
			],
			[
				'Multiple PHPDoc @var tags above single variable assignment are not supported.',
				23,
			],
			[
				'Variable $list in PHPDoc tag @var does not match any variable in the foreach loop: $key, $var',
				29,
			],
			[
				'Variable $foo in PHPDoc tag @var does not match any variable in the foreach loop: $key, $val',
				66,
			],
			[
				'PHPDoc tag @var above foreach loop does not specify variable name.',
				71,
			],
			[
				'PHPDoc tag @var above multiple static variables does not specify variable name.',
				85,
			],
			[
				'PHPDoc tag @var above multiple static variables does not specify variable name.',
				91,
			],
			[
				'PHPDoc tag @var above multiple static variables does not specify variable name.',
				91,
			],
			[
				'Variable $foo in PHPDoc tag @var does not match any static variable: $test',
				94,
			],
			[
				'PHPDoc tag @var does not specify variable name.',
				103,
			],
			[
				'Variable $foo in PHPDoc tag @var does not exist.',
				109,
			],
		]);
	}

}
