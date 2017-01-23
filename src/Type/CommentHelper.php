<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;

class CommentHelper
{

	/**
	 * @param \PhpParser\Node $node
	 * @return string|null
	 */
	public static function getDocComment(Node $node)
	{
		$phpDoc = $node->getDocComment();
		if ($phpDoc !== null) {
			return $phpDoc->getText();
		}

		$comments = $node->getAttribute('comments');
		if ($comments === null) {
			return null;
		}
		return $comments[count($comments) - 1]->getText();
	}

}
