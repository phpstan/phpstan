<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment\Exception;

use PhpParser\Comment;

/**
 * Class InvalidIgnoreNextLineNodeException
 *
 * @author Marcel Kempf <marcel.kempf@check24.de>
 */
final class InvalidIgnoreNextLineNodeException extends \Exception
{

	public function __construct(Comment $invalidComment, string $nodeType)
	{
		parent::__construct(
			sprintf(
				'Ignore next line comment at line %d is not valid for nodes of type %s',
				$invalidComment->getLine(),
				$nodeType
			)
		);
	}

}
