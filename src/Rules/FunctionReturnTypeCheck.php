<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

class FunctionReturnTypeCheck
{

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PHPStan\Type\Type $returnType
	 * @param \PhpParser\Node\Expr|null $returnValue
	 * @param string $emptyReturnStatementMessage
	 * @param string $voidMessage
	 * @param string $typeMismatchMessage
	 * @return string[]
	 */
	public function checkReturnType(
		Scope $scope,
		Type $returnType,
		Expr $returnValue = null,
		string $emptyReturnStatementMessage,
		string $voidMessage,
		string $typeMismatchMessage
	): array
	{
		if ($returnValue === null) {
			if ($returnType instanceof VoidType || $returnType instanceof MixedType) {
				return [];
			}

			return [
				sprintf(
					$emptyReturnStatementMessage,
					$returnType->describe()
				),
			];
		}

		$returnValueType = $scope->getType($returnValue);
		if ($returnType instanceof VoidType) {
			return [
				sprintf(
					$voidMessage,
					$returnValueType->describe()
				),
			];
		}

		if (!$returnType->accepts($returnValueType)) {
			return [
				sprintf(
					$typeMismatchMessage,
					$returnType->describe(),
					$returnValueType->describe()
				),
			];
		}

		return [];
	}

}
