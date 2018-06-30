<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

class ImpossibleCheckTypeHelper
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function __construct(TypeSpecifier $typeSpecifier)
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function findSpecifiedType(
		Scope $scope,
		Expr $node
	): ?bool
	{
		if (
			$node instanceof FuncCall
			&& count($node->args) > 0
		) {
			if ($node->name instanceof \PhpParser\Node\Name) {
				$functionName = strtolower((string) $node->name);
				if ($functionName === 'is_numeric') {
					$argType = $scope->getType($node->args[0]->value);
					if (count(\PHPStan\Type\TypeUtils::getConstantScalars($argType)) > 0) {
						return !$argType->toNumber() instanceof ErrorType;
					}

					if (!(new StringType())->isSuperTypeOf($argType)->no()) {
						return null;
					}
				} elseif ($functionName === 'defined') {
					return null;
				}
			}
		}

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($scope, $node, TypeSpecifierContext::createTruthy());
		$sureTypes = $specifiedTypes->getSureTypes();
		$sureNotTypes = $specifiedTypes->getSureNotTypes();

		$isSpecified = function (Expr $expr) use ($scope, $node): bool {
			return (
				$node instanceof FuncCall
				|| $node instanceof MethodCall
				|| $node instanceof Expr\StaticCall
			) && $scope->isSpecified($expr);
		};

		if (count($sureTypes) === 1) {
			$sureType = reset($sureTypes);
			if ($isSpecified($sureType[0])) {
				return null;
			}

			$argumentType = $scope->getType($sureType[0]);

			/** @var \PHPStan\Type\Type $resultType */
			$resultType = $sureType[1];

			$isSuperType = $resultType->isSuperTypeOf($argumentType);
			if ($isSuperType->yes()) {
				return true;
			} elseif ($isSuperType->no()) {
				return false;
			}

			return null;
		} elseif (count($sureNotTypes) === 1) {
			$sureNotType = reset($sureNotTypes);
			if ($isSpecified($sureNotType[0])) {
				return null;
			}

			$argumentType = $scope->getType($sureNotType[0]);

			/** @var \PHPStan\Type\Type $resultType */
			$resultType = $sureNotType[1];

			$isSuperType = $resultType->isSuperTypeOf($argumentType);
			if ($isSuperType->yes()) {
				return false;
			} elseif ($isSuperType->no()) {
				return true;
			}

			return null;
		} elseif (count($sureTypes) > 0) {
			foreach ($sureTypes as $sureType) {
				if ($isSpecified($sureType[0])) {
					return null;
				}
			}
			$types = TypeCombinator::union(...array_map(function ($sureType) {
				return $sureType[1];
			}, array_values($sureTypes)));
			if ($types instanceof NeverType) {
				return false;
			}
		} elseif (count($sureNotTypes) > 0) {
			foreach ($sureNotTypes as $sureNotType) {
				if ($isSpecified($sureNotType[0])) {
					return null;
				}
			}
			$types = TypeCombinator::union(...array_map(function ($sureNotType) {
				return $sureNotType[1];
			}, array_values($sureNotTypes)));
			if ($types instanceof NeverType) {
				return true;
			}
		}

		return null;
	}

	/**
	 * @param Scope $scope
	 * @param \PhpParser\Node\Arg[] $args
	 * @return string
	 */
	public function getArgumentsDescription(
		Scope $scope,
		array $args
	): string
	{
		if (count($args) === 0) {
			return '';
		}

		$descriptions = array_map(function (Arg $arg) use ($scope): string {
			return $scope->getType($arg->value)->describe(VerbosityLevel::value());
		}, $args);

		if (count($descriptions) < 3) {
			return sprintf(' with %s', implode(' and ', $descriptions));
		}

		$lastDescription = array_pop($descriptions);

		return sprintf(
			' with arguments %s and %s',
			implode(', ', $descriptions),
			$lastDescription
		);
	}

}
