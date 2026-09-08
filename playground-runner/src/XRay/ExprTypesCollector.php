<?php declare(strict_types = 1);

namespace PHPStanPlayground\XRay;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\VirtualNode;
use PHPStan\Type\VerbosityLevel;
use function is_string;

/**
 * Records the type PHPStan resolved for every expression it visits, keyed by
 * the expression's byte range so it can be matched to the AST from
 * AstNodesCollector.
 *
 * @implements Collector<Node\Expr, array{int, int, string, string}>
 */
final class ExprTypesCollector implements Collector
{

	public function getNodeType(): string
	{
		return Node\Expr::class;
	}

	/**
	 * @return array{int, int, string, string}|null
	 */
	public function processNode(Node $node, Scope $scope): ?array
	{
		if ($node instanceof VirtualNode) {
			return null;
		}
		if (!$node->hasAttribute('startFilePos') || !$node->hasAttribute('endFilePos')) {
			return null;
		}

		// The target of a first assignment is visited before the variable exists
		// in the scope; PHPStan would describe it as *ERROR*.
		if ($node instanceof Node\Expr\Variable && is_string($node->name) && $scope->hasVariableType($node->name)->no()) {
			return null;
		}

		return [
			$node->getStartFilePos(),
			$node->getEndFilePos() + 1,
			NodeKind::of($node),
			$scope->getType($node)->describe(VerbosityLevel::precise()),
		];
	}

}
