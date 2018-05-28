<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class VariableCloningOfPossiblyNullRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Clone_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Clone_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$type = $scope->getType($node->expr);
		if (!$type instanceof UnionType) {
			return [];
		}

		if (!\PHPStan\Type\TypeCombinator::containsNull($type)) {
			return [];
		}

		if ($node->expr instanceof Variable && is_string($node->expr->name)) {
			return [
				sprintf(
					'Cloning possibly null variable $%s of type %s.',
					$node->expr->name,
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [
			sprintf('Cloning possibly null value of type %s.', $type->describe(VerbosityLevel::typeOnly())),
		];
	}

}
