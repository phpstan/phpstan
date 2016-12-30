<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

class FunctionReturnTypeCheck
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PHPStan\Type\Type $returnType
	 * @param \PhpParser\Node\Expr|null $returnValue
	 * @param string $emptyReturnStatementMessage
	 * @param string $voidMessage
	 * @param string $typeMismatchMessage
	 * @param bool $isAnonymousFunction
	 * @return string[]
	 */
	public function checkReturnType(
		Scope $scope,
		Type $returnType,
		Expr $returnValue = null,
		string $emptyReturnStatementMessage,
		string $voidMessage,
		string $typeMismatchMessage,
		bool $isAnonymousFunction = false
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
		if ($returnType instanceof ThisType && !$returnValueType instanceof ThisType) {
			if ($returnType->isNullable() && $returnValueType instanceof NullType) {
				return [];
			}
			if (
				$returnValue instanceof Expr\Variable
				&& is_string($returnValue->name)
				&& $returnValue->name === 'this'
			) {
				return [];
			}

			return [
				sprintf(
					$typeMismatchMessage,
					'$this',
					$this->printer->prettyPrintExpr($returnValue)
				),
			];
		}

		if ($returnType instanceof VoidType) {
			return [
				sprintf(
					$voidMessage,
					$returnValueType->describe()
				),
			];
		}

		if (!$returnType->accepts($returnValueType) && (!$isAnonymousFunction || $returnValueType->isDocumentableNatively())) {
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
