<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

class NonexistentOffsetInArrayDimFetchRule implements \PHPStan\Rules\Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayDimFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrayDimFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if ($node->dim !== null) {
			$dimType = $scope->getType($node->dim);
			$unknownClassPattern = sprintf('Access to offset %s on an unknown class %%s.', $dimType->describe(VerbosityLevel::value()));
		} else {
			$dimType = null;
			$unknownClassPattern = 'Access to an offset on an unknown class %s.';
		}

		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->var,
			$unknownClassPattern,
			static function (Type $type) use ($dimType): bool {
				if ($dimType === null) {
					return $type->isOffsetAccessible()->yes();
				}

				return $type->isOffsetAccessible()->yes() && $type->hasOffsetValueType($dimType)->yes();
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}

		$isOffsetAccessible = $type->isOffsetAccessible();

		if ($scope->isInExpressionAssign($node) && !$isOffsetAccessible->no()) {
			return [];
		}

		if (!$isOffsetAccessible->yes()) {
			if ($dimType !== null) {
				return [
					sprintf(
						'Cannot access offset %s on %s.',
						$dimType->describe(VerbosityLevel::value()),
						$type->describe(VerbosityLevel::value())
					),
				];
			}

			return [
				sprintf(
					'Cannot access an offset on %s.',
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		if ($dimType === null) {
			return [];
		}

		$hasOffsetValueType = $type->hasOffsetValueType($dimType);
		$report = $hasOffsetValueType->no();
		if ($hasOffsetValueType->maybe()) {
			$constantArrays = TypeUtils::getConstantArrays($type);
			if (count($constantArrays) > 0) {
				foreach ($constantArrays as $constantArray) {
					if ($constantArray->hasOffsetValueType($dimType)->no()) {
						$report = true;
						break;
					}
				}
			}
		}

		if ($report) {
			return [
				sprintf('Offset %s does not exist on %s.', $dimType->describe(VerbosityLevel::value()), $type->describe(VerbosityLevel::value())),
			];
		}

		return [];
	}

}
