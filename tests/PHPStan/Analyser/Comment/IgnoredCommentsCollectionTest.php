<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\If_;
use PHPUnit\Framework\TestCase;

final class IgnoredCommentsCollectionTest extends TestCase
{

	public function dataIsIgnoredCheck(): array
	{
		$returnStmtNode = new If_(
			new FuncCall(new Name('doSomething'), [], ['startLine' => 5, 'endLine' => 10]),
			[],
			['startLine' => 4, 'endLine' => 19]
		);

		return [
			'ignored next line' => [
				IgnoreComment::createIgnoreNextLine(new Comment(''), $returnStmtNode),
				10,
				true,
			],
			'error before comment' => [
				IgnoreComment::createIgnoreNextLine(new Comment(''), $returnStmtNode),
				3,
				false,
			],
			'error after comment' => [
				IgnoreComment::createIgnoreNextLine(new Comment(''), $returnStmtNode),
				11,
				false,
			],

			'ignore message' => [
				IgnoreComment::createIgnoreMessage(new Comment(''), $returnStmtNode, 'Function doSomething not found.'),
				10,
				true,
			],
			'error message not matching' => [
				IgnoreComment::createIgnoreMessage(new Comment(''), $returnStmtNode, 'Function doFoo not found.'),
				10,
				false,
			],
			'error message before comment' => [
				IgnoreComment::createIgnoreMessage(new Comment(''), $returnStmtNode, 'Function doSomething not found.'),
				3,
				false,
			],
			'error message after comment' => [
				IgnoreComment::createIgnoreMessage(new Comment(''), $returnStmtNode, 'Function doSomething not found.'),
				11,
				false,
			],

			'ignore message pattern' => [
				IgnoreComment::createIgnoreRegexp(new Comment(''), $returnStmtNode, '^Function [a-zA-Z]+ not found\.$'),
				10,
				true,
			],
			'error message pattern not matching' => [
				IgnoreComment::createIgnoreRegexp(new Comment(''), $returnStmtNode, '^Function [0-9]+ not found\.$'),
				10,
				false,
			],
			'error message pattern before comment' => [
				IgnoreComment::createIgnoreRegexp(new Comment(''), $returnStmtNode, '^Function [a-zA-Z]+ not found\.$'),
				3,
				false,
			],
			'error message pattern after comment' => [
				IgnoreComment::createIgnoreRegexp(new Comment(''), $returnStmtNode, '^Function [a-zA-Z]+ not found\.$'),
				11,
				false,
			],
		];
	}

	/**
	 * @dataProvider dataIsIgnoredCheck
	 * @param IgnoreComment $ignoreComment
	 * @param int $line
	 * @param bool $expectErrorToBeIgnored
	 */
	public function testIsIgnoredCheck(
		IgnoreComment $ignoreComment,
		int $line,
		bool $expectErrorToBeIgnored
	): void
	{
		$subject = new IgnoreCommentsCollection();
		$subject->add($ignoreComment);

		$callToNonExistingMethodNode = new \PhpParser\Node\Expr\FuncCall(
			new \PhpParser\Node\Name('doSomething'),
			[],
			['startLine' => $line, 'endLine' => $line]
		);

		$this->assertSame(
			$expectErrorToBeIgnored,
			$subject->isIgnored($callToNonExistingMethodNode, 'Function doSomething not found.')
		);
	}

}
