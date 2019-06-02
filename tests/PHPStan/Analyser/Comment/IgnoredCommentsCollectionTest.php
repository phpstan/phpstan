<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PHPUnit\Framework\TestCase;

final class IgnoredCommentsCollectionTest extends TestCase
{

	/**
	 * Test the detection for ignored rules
	 *
	 * @param bool $ignoreNextLine
	 * @param int $line
	 * @param bool $expectErrorToBeIgnored
	 * @param string $messageToIgnore
	 * @param bool $isRegexp
	 *
	 * @dataProvider errorNodesDataProvider
	 *
	 * @return void
	 */
	public function testIsIgnoredCheck(
		bool $ignoreNextLine,
		int $line,
		bool $expectErrorToBeIgnored,
		string $messageToIgnore = '',
		bool $isRegexp = false
	): void
	{
		$methodNode = new \PhpParser\Node\Stmt\ClassMethod(
			'helloWorld',
			[],
			['startLine' => 4, 'endLine' => 19]
		);
		$ignoreComment = new IgnoreComment(
			new Comment(''),
			$methodNode,
			$ignoreNextLine,
			$messageToIgnore,
			$isRegexp
		);

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

	public function errorNodesDataProvider(): array
	{
		return [
			'ignored next line'                => [
				'ignoreNextLine' => true,
				'line' => 10,
				'expectErrorToBeIgnored' => true,
			],
			'error before comment'         => [
				'ignoreNextLine' => true,
				'line' => 3,
				'expectErrorToBeIgnored' => false,
			],
			'error after comment'          => [
				'ignoreNextLine' => true,
				'line' => 20,
				'expectErrorToBeIgnored' => false,
			],

			'ignore message' => [
				'ignoreNextLine' => false,
				'line' => 10,
				'expectErrorToBeIgnored' => true,
				'messageToIgnore' => 'Function doSomething not found.',
			],
			'error message not matching' => [
				'ignoreNextLine' => false,
				'line' => 10,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => 'Function doFoo not found.',
			],
			'error message before comment' => [
				'ignoreNextLine' => false,
				'line' => 3,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => 'Function doSomething not found.',
			],
			'error message after comment' => [
				'ignoreNextLine' => false,
				'line' => 20,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => 'Function doSomething not found.',
			],

			'ignore message pattern' => [
				'ignoreNextLine' => false,
				'line' => 10,
				'expectErrorToBeIgnored' => true,
				'messageToIgnore' => '^Function [a-zA-Z]+ not found\.$',
				'isRegexp' => true,
			],
			'error message pattern not matching' => [
				'ignoreNextLine' => false,
				'line' => 10,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => '^Function [0-9]+ not found\.$',
				'isRegexp' => true,
			],
			'error message pattern before comment' => [
				'ignoreNextLine' => false,
				'line' => 3,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => '^Function [a-zA-Z]+ not found\.$',
				'isRegexp' => true,
			],
			'error message pattern after comment' => [
				'ignoreNextLine' => false,
				'line' => 20,
				'expectErrorToBeIgnored' => false,
				'messageToIgnore' => '^Function [a-zA-Z]+ not found\.$',
				'isRegexp' => true,
			],
		];
	}

}
