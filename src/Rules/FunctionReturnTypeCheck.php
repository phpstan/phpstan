<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

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
	 * @return string[]
	 */
	public function checkReturnType(
		Scope $scope,
		Type $returnType,
		?Expr $returnValue,
		string $emptyReturnStatementMessage,
		string $voidMessage,
		string $typeMismatchMessage,
		bool $isGenerator
	): array
	{
		if ($isGenerator) {
			return [];
		}

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

		if (!$this->ruleLevelHelper->accepts($returnType, $returnValueType)) {
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
