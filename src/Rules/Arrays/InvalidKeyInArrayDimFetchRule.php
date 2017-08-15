<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;

class InvalidKeyInArrayDimFetchRule implements \PHPStan\Rules\Rule
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
		if ($node->dim === null) {
			return [];
		}

		$varType = $scope->getType($node->var);
		if (!$varType instanceof ArrayType) {
			return [];
		}

		$dimensionType = $scope->getType($node->dim);
		if (!AllowedArrayKeysTypes::getType()->accepts($dimensionType)) {
			return [
				sprintf('Invalid array key type %s.', $dimensionType->describe()),
			];
		}

		return [];
	}

}
