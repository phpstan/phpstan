<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PhpParser\Node;

class CommentParser
{

	public const ANNOTATION_REGEX_IGNORE_NEXT_LINE = '/[(\/\*\*)|(\/\/)] (\@phpstan\-ignore-next-line)( \*\/)?/';
	public const ANNOTATION_REGEX_IGNORE_MESSAGE = '/[(\/\*\*)|(\/\/)] \@phpstan\-ignore-message ([^\*\/]+)( \*\/)?/';
	public const ANNOTATION_REGEX_IGNORE_MESSAGE_REGEXP = '/[(\/\*\*)|(\/\/)] \@phpstan\-ignore-message-regexp? ([^\*\/]+)( \*\/)?/';

	public function parseIgnoreComment(Comment $comment, Node $node): ?IgnoreComment
	{
		$commentText = trim($comment->getText());

		if (strpos($commentText, '@phpstan-ignore-') === false) {
			return null;
		}

		preg_match(
			self::ANNOTATION_REGEX_IGNORE_NEXT_LINE,
			$commentText,
			$ignoreNextLineMatches
		);

		if (count($ignoreNextLineMatches) > 0) {
			$this->validateNode($comment, $node);
			return IgnoreComment::createIgnoreNextLine($comment, $node);
		}

		preg_match(
			self::ANNOTATION_REGEX_IGNORE_MESSAGE,
			$commentText,
			$ignoreMessageMatches
		);

		if (count($ignoreMessageMatches) > 0) {
			$this->validateNode($comment, $node);
			return IgnoreComment::createIgnoreMessage($comment, $node, trim($ignoreMessageMatches[1]));
		}

		preg_match(
			self::ANNOTATION_REGEX_IGNORE_MESSAGE_REGEXP,
			$commentText,
			$ignoreMessageRegexpMatches
		);

		if (count($ignoreMessageRegexpMatches) > 0) {
			$this->validateNode($comment, $node);
			return IgnoreComment::createIgnoreRegexp($comment, $node, trim($ignoreMessageRegexpMatches[1]));
		}

		return null;
	}

	private function validateNode(Comment $comment, Node $node): void
	{
		$invalidNodes = [
			Node\Stmt\ClassLike::class,
			Node\Stmt\ClassMethod::class,
			Node\Stmt\Function_::class,
		];

		foreach ($invalidNodes as $invalidNode) {
			if ($node instanceof $invalidNode) {
				throw new \PHPStan\Analyser\Comment\Exception\InvalidIgnoreNextLineNodeException($comment, $node->getType());
			}
		}
	}

}
