<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;
use PHPStan\Type\VerbosityLevel;

class NonexistentOffsetInArrayDimFetchRule implements \PHPStan\Rules\Rule
{

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
		$varType = $scope->getType($node->var);
		if ($varType->isOffsetAccessible()->no()) {
			return [
				sprintf('Cannot access array offset on %s.', $varType->describe(VerbosityLevel::typeOnly())),
			];
		}

		if ($node->dim === null) {
			return [];
		}

		if ($scope->isInExpressionAssign($node)) {
			return [];
		}

		$dimType = $scope->getType($node->dim);
		if ($varType->getOffsetValueType($dimType) instanceof ErrorType) {
			return [
				sprintf('Offset %s does not exist on %s.', $dimType->describe(VerbosityLevel::value()), $varType->describe(VerbosityLevel::value())),
			];
		}

		return [];
	}

}
