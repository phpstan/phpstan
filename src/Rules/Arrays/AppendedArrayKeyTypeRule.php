<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class AppendedArrayKeyTypeRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkUnionTypes;

	public function __construct(bool $checkUnionTypes)
	{
		$this->checkUnionTypes = $checkUnionTypes;
	}

	public function getNodeType(): string
	{
		return Assign::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Assign $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!($node->var instanceof ArrayDimFetch)) {
			return [];
		}

		$arrayType = $scope->getType($node->var->var);
		if (!$arrayType instanceof ArrayType) {
			return [];
		}

		if ($arrayType->isItemTypeInferredFromLiteralArray()) {
			return [];
		}

		if ($node->var->dim !== null) {
			$dimensionType = $scope->getType($node->var->dim);
			$isValidKey = AllowedArrayKeysTypes::getType()->isSuperTypeOf($dimensionType);
			if (!$isValidKey->yes()) {
				// already handled by InvalidKeyInArrayDimFetchRule
				return [];
			}

			$keyType = ArrayType::castToArrayKeyType($dimensionType);
			if (!$this->checkUnionTypes && $keyType instanceof UnionType) {
				return [];
			}
		} else {
			$keyType = new IntegerType();
		}

		if (!$arrayType->getIterableKeyType()->isSuperTypeOf($keyType)->yes()) {
			return [
				sprintf(
					'Array (%s) does not accept key %s.',
					$arrayType->describe(VerbosityLevel::typeOnly()),
					$keyType->describe(VerbosityLevel::value())
				),
			];
		}

		return [];
	}

}
