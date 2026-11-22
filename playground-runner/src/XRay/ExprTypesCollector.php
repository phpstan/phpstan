<?php declare(strict_types = 1);

namespace PHPStanPlayground\XRay;

use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\VirtualNode;
use PHPStan\Type\VerbosityLevel;
use function array_unique;
use function array_values;
use function is_string;

/**
 * Records the type PHPStan resolved for every expression it visits, with the
 * throw points and impure points of its ExpressionResult, keyed by the
 * expression's byte range so it can be matched to the AST from
 * AstNodesCollector.
 *
 * @implements Collector<Node\Expr, array{int, int, string, string, list<string>, list<string>}>
 */
final class ExprTypesCollector implements Collector
{

	public function getNodeType(): string
	{
		return Node\Expr::class;
	}

	/**
	 * @return array{int, int, string, string, list<string>, list<string>}|null
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

		// Collectors run once the expression has been processed, so its result
		// (throw points and impure points included) is already stored.
		$throwPoints = [];
		$impurePoints = [];
		$result = $scope instanceof MutatingScope ? $scope->getCurrentExpressionResultStorage()?->findExpressionResult($node) : null;
		if ($result !== null) {
			foreach ($result->getThrowPoints() as $throwPoint) {
				// An implicit throw point is PHPStan assuming a call might throw
				// something; its type (Throwable) says nothing more.
				$throwPoints[] = $throwPoint->isExplicit() ? $throwPoint->getType()->describe(VerbosityLevel::precise()) : 'implicit';
			}
			foreach ($result->getImpurePoints() as $impurePoint) {
				$impurePoints[] = $impurePoint->getDescription() . ($impurePoint->isCertain() ? '' : ' (possibly)');
			}
		}

		return [
			$node->getStartFilePos(),
			$node->getEndFilePos() + 1,
			NodeKind::of($node),
			$scope->getType($node)->describe(VerbosityLevel::precise()),
			array_values(array_unique($throwPoints)),
			array_values(array_unique($impurePoints)),
		];
	}

}
