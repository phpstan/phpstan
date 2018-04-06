<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;

class InvalidStringCastRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\Cast\String_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Cast\String_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($scope->getType($node) instanceof ErrorType) {
			return [
				sprintf(
					'Cannot cast %s to string.',
					$scope->getType($node->expr)->describe()
				),
			];
		}

		return [];
	}

}
