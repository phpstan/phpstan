<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VoidType;
use PHPStan\TypeX\Is;

class FunctionReturnTypeCheck
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		\PhpParser\PrettyPrinter\Standard $printer,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->printer = $printer;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PHPStan\Type\Type $returnType
	 * @param \PhpParser\Node\Expr|null $returnValue
	 * @param string $emptyReturnStatementMessage
	 * @param string $voidMessage
	 * @param string $typeMismatchMessage
	 * @param bool $isGenerator
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
		bool $isGenerator,
		bool $isAnonymousFunction = false
	): array
	{
		if ($returnValue === null) {
			if (
				$isGenerator
				|| Is::type($returnType, VoidType::class)
				|| Is::type($returnType, MixedType::class)
			) {
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
		if (
			TypeCombinator::removeNull($returnType) instanceof ThisType
			&& !$returnValueType instanceof ThisType
		) {
			if (TypeCombinator::containsNull($returnType) && $returnValueType instanceof \PHPStan\Type\NullType) {
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

		if (Is::type($returnType, VoidType::class)) {
			return [
				sprintf(
					$voidMessage,
					$returnValueType->describe()
				),
			];
		}

		if (!$this->ruleLevelHelper->accepts($returnType, $returnValueType) && (!$isAnonymousFunction || $returnValueType->isDocumentableNatively())) {
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
