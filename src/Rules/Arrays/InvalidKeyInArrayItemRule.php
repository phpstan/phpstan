<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;

class InvalidKeyInArrayItemRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayItem::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrayItem $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if ($node->key === null) {
			return [];
		}

		$dimensionType = $scope->getType($node->key);
		if (!AllowedArrayKeysTypes::getType()->accepts($dimensionType)) {
			return [
				sprintf('Invalid array key type %s.', $dimensionType->describe()),
			];
		}

		return [];
	}

}
