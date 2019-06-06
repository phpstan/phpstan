<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class CommentParserTest extends TestCase
{

	public function dataParseIgnoreComment(): array
	{
		return [
			'ignore next single line comment' => [
				'// @phpstan-ignore-next-line',
				true,
			],
			'ignore next single line doc block comment' => [
				'/** @phpstan-ignore-next-line */',
				true,
			],
			'ignore next multi line doc block comment' => [
				'/**' . PHP_EOL .
				' * Lorem ipsum' . PHP_EOL .
				' *' . PHP_EOL .
				' * @phpstan-ignore-next-line' . PHP_EOL .
				' */',
				true,
			],

			'ignore message single line comment' => [
				'// @phpstan-ignore-message Function doSomething not found.',
				true,
			],
			'ignore message single line comment - wrong message' => [
				'// @phpstan-ignore-message Function doSomethingElse not found.',
				false,
			],
			'ignore message single line doc block comment' => [
				'/** @phpstan-ignore-message Function doSomething not found. */',
				true,
			],
			'ignore message multi line doc block comment' => [
				'/**' . PHP_EOL .
				' * Lorem ipsum' . PHP_EOL .
				' *' . PHP_EOL .
				' * @phpstan-ignore-message Function doSomething not found.' . PHP_EOL .
				' */',
				true,
			],
			'ignore message pattern single line comment' => [
				'// @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found.',
				true,
			],
			'ignore message pattern single line comment - wrong pattern' => [
				'// @phpstan-ignore-message-regexp Function [0-9]+ not found.',
				false,
			],
			'ignore message pattern single line doc block comment' => [
				'/** @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found. */',
				true,
			],
			'ignore message pattern multi line doc block comment' => [
				'/**' . PHP_EOL .
				' * Lorem ipsum' . PHP_EOL .
				' *' . PHP_EOL .
				' * @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found.' . PHP_EOL .
				' */',
				true,
			],
		];
	}

	/**
	 * @dataProvider dataParseIgnoreComment
	 * @param string $commentText
	 * @param bool $ignores
	 */
	public function testParseIgnoreComment(
		string $commentText,
		bool $ignores
	): void
	{
		$comment = new Comment($commentText);
		$callToNonExistingMethodNode = new \PhpParser\Node\Expr\FuncCall(
			new \PhpParser\Node\Name('doSomething'),
			[],
			['startLine' => 10, 'endLine' => 10]
		);

		$subject = new CommentParser();

		$ignoreComment = $subject->parseIgnoreComment($comment, $callToNonExistingMethodNode);
		$this->assertNotNull($ignoreComment);
		$this->assertSame($ignores, $ignoreComment->ignores($callToNonExistingMethodNode, 'Function doSomething not found.'));
	}

	public function dataInvalidIgnoreComment(): array
	{
		return [
			['@phpstan-ignore'],
			['@phpstan-'],
			['@foo'],
			['phpstan-ignore-'],
		];
	}

	/**
	 * @dataProvider dataInvalidIgnoreComment
	 * @param string $commentText
	 */
	public function testInvalidIgnoreComment(string $commentText): void
	{
		$comment = new Comment($commentText);
		$callToNonExistingMethodNode = new \PhpParser\Node\Expr\FuncCall(
			new \PhpParser\Node\Name('doSomething'),
			[],
			['startLine' => 10, 'endLine' => 10]
		);

		$subject = new CommentParser();
		$ignoreComment = $subject->parseIgnoreComment($comment, $callToNonExistingMethodNode);
		$this->assertNull($ignoreComment);
	}

	public function dataInvalidNodeType(): array
	{
		return [
			'Class node' => [
				'node' => new Node\Stmt\Class_('SomeClassToAnalyse'),
				'commentText' => '// @phpstan-ignore-next-line',
			],
			'Class method node' => [
				'node' => new Node\Stmt\ClassMethod('someClassMethod'),
				'commentText' => '// @phpstan-ignore-message Function doSomething not found.',
			],
			'Function node' => [
				'node' => new Node\Stmt\Function_('someFunction'),
				'commentText' => '// @phpstan-ignore-message-regexp ^Function [a-zA-Z]+ not found.$',
			],
		];
	}

	/**
	 * @dataProvider dataInvalidNodeType
	 * @param Node $node
	 * @param string $commentText
	 */
	public function testInvalidNodeType(Node $node, string $commentText): void
	{
		$comment = new Comment($commentText);
		$expectedException = new \PHPStan\Analyser\Comment\Exception\InvalidIgnoreNextLineNodeException(
			$comment,
			$node->getType()
		);

		$this->expectExceptionObject($expectedException);

		$subject = new CommentParser();
		$subject->parseIgnoreComment($comment, $node);
	}

}
