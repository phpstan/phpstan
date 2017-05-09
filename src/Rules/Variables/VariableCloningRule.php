<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class VariableCloningRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Clone_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Clone_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$type = $scope->getType($node->expr);

		if ($this->isClonable($type)) {
			return [];
		}

		if ($node->expr instanceof Variable) {
			return [
				sprintf(
					'Cannot clone non-object variable $%s of type %s.',
					$node->expr->name,
					$type->describe()
				),
			];
		}

		return [
			sprintf('Cannot clone %s.', $type->describe()),
		];
	}

	private function isClonable(Type $type): bool
	{
		if ($type instanceof MixedType) {
			return true;
		}

		if ($type instanceof UnionType) {
			foreach ($type->getTypes() as $innerType) {
				if ($this->isClonable($innerType)) {
					return true;
				}
			}
		}

		return $type->getClass() !== null;
	}

}
