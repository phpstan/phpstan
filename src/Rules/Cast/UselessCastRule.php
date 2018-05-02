<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

class UselessCastRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Cast::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Cast $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$castType = $scope->getType($node);
		if ($castType instanceof ErrorType) {
			return [];
		}
		$castType = TypeUtils::generalizeType($castType);

		$expressionType = $scope->getType($node->expr);
		if ($castType->isSuperTypeOf($expressionType)->yes()) {
			return [
				sprintf(
					'Casting to %s something that\'s already %s.',
					$castType->describe(VerbosityLevel::typeOnly()),
					$expressionType->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
