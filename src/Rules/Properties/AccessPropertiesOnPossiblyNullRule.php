<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class AccessPropertiesOnPossiblyNullRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		$type = $scope->getType($node->var);
		if (!$type instanceof UnionType) {
			return [];
		}

		if (\PHPStan\Type\TypeCombinator::containsNull($type)) {
			return [
				sprintf(
					'Accessing property $%s on possibly null value of type %s.',
					$node->name->name,
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
