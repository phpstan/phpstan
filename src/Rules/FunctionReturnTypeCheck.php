<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VoidType;
use PHPStan\Rules\RuleErrorBuilder;

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
	 * @return array<mixed>|\PHPStan\Rules\RuleError[]
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
				|| $returnType instanceof VoidType
				|| $returnType instanceof MixedType
			) {
				return [];
			}

			return [
				RuleErrorBuilder::message(sprintf(
					$emptyReturnStatementMessage,
					$returnType->describe()
				))->identifier('returnType.emptyReturn')->build(),
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
				RuleErrorBuilder::message(sprintf(
					$typeMismatchMessage,
					'$this',
					$this->printer->prettyPrintExpr($returnValue)
				))->identifier('returnType.thisMismatch')->build(),
			];
		}

		if ($returnType instanceof VoidType) {
			return [
				RuleErrorBuilder::message(sprintf(
					$voidMessage,
					$returnValueType->describe()
				))->identifier('returnType.voidReturn')->build(),
			];
		}

		if (!$this->ruleLevelHelper->accepts($returnType, $returnValueType) && (!$isAnonymousFunction || $returnValueType->isDocumentableNatively())) {
			return [
				RuleErrorBuilder::message(sprintf(
					$typeMismatchMessage,
					$returnType->describe(),
					$returnValueType->describe()
				))->identifier('returnType.typeMismatch')->build(),
			];
		}

		return [];
	}

}
