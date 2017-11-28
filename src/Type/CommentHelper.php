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

		return null;
	}

}
