<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PhpParser\Node;

class CommentParser
{

	public const ANNOTATION_REGEX_IGNORE_NEXT_LINE = '/[(\/\*\*)|(\/\/)] (\@phpstan\-ignore-next-line)( \*\/)?/';
	public const ANNOTATION_REGEX_IGNORE_MESSAGE = '/[(\/\*\*)|(\/\/)] \@phpstan\-ignore-message ([^\*\/]+)( \*\/)?/';
	public const ANNOTATION_REGEX_IGNORE_MESSAGE_REGEXP = '/[(\/\*\*)|(\/\/)] \@phpstan\-ignore-message-regexp? ([^\*\/]+)( \*\/)?/';

	/**
	 * Try to parse a comment and create an ignored comment
	 *
	 * @param Comment $comment
	 * @param Node $node
	 *
	 * @return IgnoreComment|null
	 */
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
			return new IgnoreComment($comment, $node, true);
		}

		preg_match(
			self::ANNOTATION_REGEX_IGNORE_MESSAGE,
			$commentText,
			$ignoreMessageMatches
		);

		if (count($ignoreMessageMatches) > 0) {
			return new IgnoreComment($comment, $node, false, trim($ignoreMessageMatches[1]));
		}

		preg_match(
			self::ANNOTATION_REGEX_IGNORE_MESSAGE_REGEXP,
			$commentText,
			$ignoreMessageRegexpMatches
		);

		if (count($ignoreMessageRegexpMatches) > 0) {
			return new IgnoreComment($comment, $node, false, trim($ignoreMessageRegexpMatches[1]), true);
		}

		return null;
	}

}
