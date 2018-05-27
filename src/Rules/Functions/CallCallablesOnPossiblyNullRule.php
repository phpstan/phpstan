<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class CallCallablesOnPossiblyNullRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		Scope $scope
	): array
	{
		if (!$node->name instanceof \PhpParser\Node\Expr) {
			return [];
		}

		$type = $scope->getType($node->name);
		if (!$type instanceof UnionType) {
			return [];
		}

		if (\PHPStan\Type\TypeCombinator::containsNull($type)) {
			return [
				sprintf(
					'Invoking possibly null value of type %s.',
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
