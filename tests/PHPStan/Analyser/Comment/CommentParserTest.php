<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PHPUnit\Framework\TestCase;

final class CommentParserTest extends TestCase
{

	/**
	 * Test if a comment is parsed to an ignore comment
	 *
	 * @param string $commentText
	 * @param bool $expectIgnoreNextLine
	 * @param string $expectedMessage
	 * @param bool $expectRegexp
	 *
	 * @dataProvider commentsDataProvider
	 *
	 * @return void
	 */
	public function testParseIgnoreComment(
		string $commentText,
		bool $expectIgnoreNextLine,
		string $expectedMessage = '',
		bool $expectRegexp = false
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

		$this->assertInstanceOf(IgnoreComment::class, $ignoreComment);
		$this->assertSame($expectIgnoreNextLine, $ignoreComment->shouldIgnoreNextLine());
		$this->assertSame($expectRegexp, $ignoreComment->isRegexp());
		$this->assertSame($expectedMessage, $ignoreComment->getMessage());
	}

	/**
	 * Test if invalid ignore comments are parsed to a null value
	 *
	 * @param string $commentText
	 *
	 * @dataProvider invalidIgnoreCommentsDataProvider
	 *
	 * @return void
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

	public function commentsDataProvider(): array
	{
		return [
			'ignore next single line comment'           => [
				'commentText'          => '// @phpstan-ignore-next-line',
				'expectIgnoreNextLine' => true,
			],
			'ignore next single line doc block comment' => [
				'commentText'          => '/** @phpstan-ignore-next-line */',
				'expectIgnoreNextLine' => true,
			],
			'ignore next multi line doc block comment'  => [
				'commentText'          => '/**' . PHP_EOL .
					' * Lorem ipsum' . PHP_EOL .
					' *' . PHP_EOL .
					' * @phpstan-ignore-next-line' . PHP_EOL .
					' */',
				'expectIgnoreNextLine' => true,
			],

			'ignore message single line comment'           => [
				'commentText'          => '// @phpstan-ignore-message Function doSomething not found.',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function doSomething not found.',
			],
			'ignore message single line doc block comment' => [
				'commentText'          => '/** @phpstan-ignore-message Function doSomething not found. */',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function doSomething not found.',
			],
			'ignore message multi line doc block comment'  => [
				'commentText'          => '/**' . PHP_EOL .
					' * Lorem ipsum' . PHP_EOL .
					' *' . PHP_EOL .
					' * @phpstan-ignore-message Function doSomething not found.' . PHP_EOL .
					' */',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function doSomething not found.',
			],

			'ignore message pattern single line comment'           => [
				'commentText'          => '// @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found.',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function [a-zA-Z]+ not found.',
				'expectRegexp'         => true,
			],
			'ignore message pattern single line doc block comment' => [
				'commentText'          => '/** @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found. */',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function [a-zA-Z]+ not found.',
				'expectRegexp'         => true,
			],
			'ignore message pattern multi line doc block comment'  => [
				'commentText'          => '/**' . PHP_EOL .
					' * Lorem ipsum' . PHP_EOL .
					' *' . PHP_EOL .
					' * @phpstan-ignore-message-regexp Function [a-zA-Z]+ not found.' . PHP_EOL .
					' */',
				'expectIgnoreNextLine' => false,
				'expectedMessage'      => 'Function [a-zA-Z]+ not found.',
				'expectRegexp'         => true,
			],
		];
	}

	public function invalidIgnoreCommentsDataProvider(): array
	{
		return [
			['@phpstan-ignore'],
			['@phpstan-'],
			['@foo'],
			['phpstan-ignore-'],
		];
	}

}
