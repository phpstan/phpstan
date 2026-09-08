<?php declare(strict_types = 1);

namespace PHPStanPlayground\XRay;

use PhpParser\Node;
use function get_class;
use function str_starts_with;
use function strlen;
use function substr;

final class NodeKind
{

	private const NODE_NAMESPACE = 'PhpParser\\Node\\';

	/**
	 * Class name without the PhpParser\Node\ prefix, e.g. "Expr\Variable".
	 */
	public static function of(Node $node): string
	{
		$class = get_class($node);
		if (str_starts_with($class, self::NODE_NAMESPACE)) {
			return substr($class, strlen(self::NODE_NAMESPACE));
		}

		return $class;
	}

}
