<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

class ImpossibleCheckTypeHelper
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function __construct(
		Broker $broker,
		TypeSpecifier $typeSpecifier
	)
	{
		$this->broker = $broker;
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
					if (count(TypeUtils::getConstantScalars($argType)) > 0) {
						return !$argType->toNumber() instanceof ErrorType;
					}

					if (!(new StringType())->isSuperTypeOf($argType)->no()) {
						return null;
					}
				} elseif ($functionName === 'defined') {
					return null;
				} elseif (
					$functionName === 'in_array'
					&& count($node->args) >= 3
				) {
					$needleType = $scope->getType($node->args[0]->value);
					$valueType = $scope->getType($node->args[1]->value)->getIterableValueType();
					$hasConstantNeedleTypes = count(TypeUtils::getConstantScalars($needleType)) > 0;
					$hasConstantHaystackTypes = count(TypeUtils::getConstantScalars($valueType)) > 0;
					if (
						$valueType->isSuperTypeOf($needleType)->yes()
						&& (
							(
								!$hasConstantNeedleTypes
								&& !$hasConstantHaystackTypes
							)
							|| $hasConstantNeedleTypes !== $hasConstantHaystackTypes
						)
					) {
						return null;
					}
				} elseif (
					$functionName === 'property_exists'
					&& count($node->args) >= 2
				) {
					$classNames = TypeUtils::getDirectClassNames(
						$scope->getType($node->args[0]->value)
					);
					foreach ($classNames as $className) {
						if (!$this->broker->hasClass($className)) {
							continue;
						}

						if (UniversalObjectCratesClassReflectionExtension::isUniversalObjectCrate(
							$this->broker,
							$this->broker->getUniversalObjectCratesClasses(),
							$this->broker->getClass($className)
						)) {
							return null;
						}
					}
				}
			}
		}

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($scope, $node, TypeSpecifierContext::createTruthy());
		$sureTypes = $specifiedTypes->getSureTypes();
		$sureNotTypes = $specifiedTypes->getSureNotTypes();

		$isSpecified = static function (Expr $expr) use ($scope, $node): bool {
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
			$types = TypeCombinator::union(...array_map(static function ($sureType) {
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
			$types = TypeCombinator::union(...array_map(static function ($sureNotType) {
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

		$descriptions = array_map(static function (Arg $arg) use ($scope): string {
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
