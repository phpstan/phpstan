<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class ArrayDimFetchOnPossiblyNullRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayDimFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrayDimFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$type = $scope->getType($node->var);
		if (!$type instanceof UnionType) {
			return [];
		}

		if (\PHPStan\Type\TypeCombinator::containsNull($type)) {
			if ($node->dim !== null) {
				return [
					sprintf(
						'Accessing offset %s on possibly null value of type %s.',
						$scope->getType($node->dim)->describe(VerbosityLevel::value()),
						$type->describe(VerbosityLevel::value())
					),
				];
			}

			return [
				sprintf(
					'Accessing an offset on possibly null value of type %s.',
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
