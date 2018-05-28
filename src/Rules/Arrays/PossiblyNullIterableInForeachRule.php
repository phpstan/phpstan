<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class PossiblyNullIterableInForeachRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\Foreach_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Foreach_ $node
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

		return [
			sprintf(
				'Iterating over a possibly null value of type %s.',
				$type->describe(VerbosityLevel::typeOnly())
			),
		];
	}

}
