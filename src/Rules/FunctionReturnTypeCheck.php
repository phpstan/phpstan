<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class FunctionReturnTypeCheck
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
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

		$isVoidSuperType = (new VoidType())->isSuperTypeOf($returnType);
		$verbosityLevel = $returnType->isCallable()->yes() || count(TypeUtils::getConstantArrays($returnType)) > 0
			? VerbosityLevel::value()
			: VerbosityLevel::typeOnly();
		if ($returnValue === null) {
			if (!$isVoidSuperType->no()) {
				return [];
			}

			return [
				sprintf(
					$emptyReturnStatementMessage,
					$returnType->describe($verbosityLevel)
				),
			];
		}

		$returnValueType = $scope->getType($returnValue);

		if ($isVoidSuperType->yes()) {
			return [
				sprintf(
					$voidMessage,
					$returnValueType->describe($verbosityLevel)
				),
			];
		}

		if (!$this->ruleLevelHelper->accepts($returnType, $returnValueType, $scope->isDeclareStrictTypes())) {
			return [
				sprintf(
					$typeMismatchMessage,
					$returnType->describe($verbosityLevel),
					$returnValueType->describe($verbosityLevel)
				),
			];
		}

		return [];
	}

}
