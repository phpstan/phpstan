<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class OffsetAccessAssignmentToScalarRule implements \PHPStan\Rules\Rule
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
		if (!$scope->isInExpressionAssign($node)) {
			return [];
		}

		$potentialDimType = $scope->getType($node->dim);

		$varTypeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->var,
			'',
			function (Type $varType) use ($potentialDimType): bool {
				$arrayDimType = $varType->setOffsetValueType($potentialDimType, new MixedType());
				return !($arrayDimType instanceof ErrorType);
			}
		);
		$varType = $varTypeResult->getType();

		$dimTypeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->dim,
			'',
			function (Type $dimType) use ($varType): bool {
				$arrayDimType = $varType->setOffsetValueType($dimType, new MixedType());
				return !($arrayDimType instanceof ErrorType);
			}
		);
		$dimType = $dimTypeResult->getType();

		$arrayDimType = $varType->setOffsetValueType($dimType, new MixedType());
		if (!($arrayDimType instanceof ErrorType)) {
			return [];
		}

		return [sprintf(
			'Cannot use value of type %s as an array',
			$varType->describe(VerbosityLevel::typeOnly())
		)];
	}

}
