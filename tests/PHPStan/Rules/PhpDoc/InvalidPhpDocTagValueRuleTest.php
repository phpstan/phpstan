<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

class InvalidPhpDocTagValueRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new InvalidPhpDocTagValueRule(
			self::getContainer()->getByType(Lexer::class),
			self::getContainer()->getByType(PhpDocParser::class)
		);
	}

	public function testRule(): void
	{
		$this->analyse(
			[__DIR__ . '/data/invalid-phpdoc.php'],
			[
				[
					'PHPDoc tag @param has invalid value (): Unexpected token "\n *", expected TOKEN_IDENTIFIER at offset 13',
					24,
				],
				[
					'PHPDoc tag @param has invalid value ($invalid): Unexpected token "$invalid", expected TOKEN_IDENTIFIER at offset 24',
					24,
				],
				[
					'PHPDoc tag @param has invalid value ($invalid Foo): Unexpected token "$invalid", expected TOKEN_IDENTIFIER at offset 43',
					24,
				],
				[
					'PHPDoc tag @param has invalid value (A & B | C $paramNameA): Unexpected token "|", expected TOKEN_VARIABLE at offset 72',
					24,
				],
				[
					'PHPDoc tag @param has invalid value ((A & B $paramNameB): Unexpected token "$paramNameB", expected \')\' at offset 105',
					24,
				],
				[
					'PHPDoc tag @param has invalid value (~A & B $paramNameC): Unexpected token "~A", expected TOKEN_IDENTIFIER at offset 127',
					24,
				],
				[
					'PHPDoc tag @var has invalid value (): Unexpected token "\n *", expected TOKEN_IDENTIFIER at offset 156',
					24,
				],
				[
					'PHPDoc tag @var has invalid value ($invalid): Unexpected token "$invalid", expected TOKEN_IDENTIFIER at offset 165',
					24,
				],
				[
					'PHPDoc tag @var has invalid value ($invalid Foo): Unexpected token "$invalid", expected TOKEN_IDENTIFIER at offset 182',
					24,
				],
				[
					'PHPDoc tag @return has invalid value (): Unexpected token "\n *", expected TOKEN_IDENTIFIER at offset 208',
					24,
				],
				[
					'PHPDoc tag @return has invalid value ([int, string]): Unexpected token "[", expected TOKEN_IDENTIFIER at offset 220',
					24,
				],
				[
					'PHPDoc tag @return has invalid value (A & B | C): Unexpected token "|", expected TOKEN_OTHER at offset 251',
					24,
				],
				[
					'PHPDoc tag @var has invalid value (\\\Foo|\Bar $test): Unexpected token "\\\\\\\Foo|\\\Bar", expected TOKEN_IDENTIFIER at offset 9',
					28,
				],
			]
		);
	}

}
