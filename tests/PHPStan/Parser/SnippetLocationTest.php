<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Error;
use PHPStan\Testing\TestCase;

class SnippetLocationTest extends TestCase
{

	public function testPhpDocIsIncludedIfPresent(): void
	{
		$fileSrc = __DIR__ . '/data/TestClass.php';
		$phpDocComment = '	/**
	 * @param string $snippet
	 * @return string
	 */';
		$body = '	private function shortenIfExceeds(int $snippet): string
	{
		if ($snippet*2 > self::SNIPPET_SIZE) {
			/** @var float $unknownData */
			$lineBreak = mb_strpos($snippet, "\n", $unknownData);
		}
		return true;';

		$error = 'Error message';
		$node = new Identifier('name', [
			'startLine' => 9,
			'endLine' => 16,
			'comments' => [
				new Doc($phpDocComment),
			],
		]);

		$this->assertEquals(
			$phpDocComment . $body,
			(new SnippetLocation($fileSrc, $node, $error))->getSnippet()
		);
	}

	public function testByNode(): void
	{
		$fileSrc = __DIR__ . '/data/TestClass.php';
		$error = new Error('Left side of || is always true', $fileSrc, 5);
		$node = new Identifier('name', [
			'startLine' => 1,
			'endLine' => 1,
		]);
		$this->assertEquals(
			'<?php $x = (1 || 0);',
			(new SnippetLocation($fileSrc, $node, $error))->getSnippet()
		);
	}

	public function testByRuleNumber(): void
	{
		$fileSrc = __DIR__ . '/data/TestClass.php';
		$error = \PHPStan\Rules\RuleErrorBuilder::message('Left side of || is always true')->line(1)->build();
		$node = new Identifier('name', [
			'startLine' => 4,
			'endLine' => 8,
		]);
		$this->assertEquals(
			'<?php $x = (1 || 0);',
			(new SnippetLocation($fileSrc, $node, $error))->getSnippet()
		);
	}

}
