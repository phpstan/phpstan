<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PhpParser\Node;

class CommentHelper
{

	public static function getDocComment(Node $node): ?string
	{
		$phpDoc = $node->getDocComment();
		if ($phpDoc !== null) {
			return $phpDoc->getText();
		}

		return null;
	}

}
