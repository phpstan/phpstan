<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\ArrayDimFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class OffsetAccessAssignOpRule implements \PHPStan\Rules\Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\AssignOp::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\AssignOp $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!$node->var instanceof ArrayDimFetch) {
			return [];
		}

		$arrayDimFetch = $node->var;

		$potentialDimType = null;
		if ($arrayDimFetch->dim !== null) {
			$potentialDimType = $scope->getType($arrayDimFetch->dim);
		}

		$varTypeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$arrayDimFetch->var,
			'',
			static function (Type $varType) use ($potentialDimType): bool {
				$arrayDimType = $varType->setOffsetValueType($potentialDimType, new MixedType());
				return !($arrayDimType instanceof ErrorType);
			}
		);
		$varType = $varTypeResult->getType();

		if ($arrayDimFetch->dim !== null) {
			$dimTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$arrayDimFetch->dim,
				'',
				static function (Type $dimType) use ($varType): bool {
					$arrayDimType = $varType->setOffsetValueType($dimType, new MixedType());
					return !($arrayDimType instanceof ErrorType);
				}
			);
			$dimType = $dimTypeResult->getType();
			if ($varType->hasOffsetValueType($dimType)->no()) {
				return [];
			}
		} else {
			$dimType = $potentialDimType;
		}

		$resultType = $varType->setOffsetValueType($dimType, new MixedType());
		if (!($resultType instanceof ErrorType)) {
			return [];
		}

		if ($dimType === null) {
			return [sprintf(
				'Cannot assign new offset to %s.',
				$varType->describe(VerbosityLevel::typeOnly())
			)];
		}

		return [sprintf(
			'Cannot assign offset %s to %s.',
			$dimType->describe(VerbosityLevel::value()),
			$varType->describe(VerbosityLevel::typeOnly())
		)];
	}

}
