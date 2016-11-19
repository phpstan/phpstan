<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;

class AppendedArrayItemTypeRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Assign::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Assign $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!($node->var instanceof ArrayDimFetch)) {
			return [];
		}

		$assignedToType = $scope->getType($node->var->var);
		if (!($assignedToType instanceof ArrayType)) {
			return [];
		}

		if ($assignedToType->isItemTypeInferredFromLiteralArray()) {
			return [];
		}

		$assignedValueType = $scope->getType($node->expr);
		if (!$assignedToType->getItemType()->accepts($assignedValueType)) {
			return [
				sprintf(
					'Array (%s) does not accept %s.',
					$assignedToType->describe(),
					$assignedValueType->describe()
				),
			];
		}

		return [];
	}

}
