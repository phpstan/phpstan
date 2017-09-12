<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;

class VariableCloningRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkNullables;

	public function __construct(bool $checkNullables)
	{
		$this->checkNullables = $checkNullables;
	}

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

		if (!$this->checkNullables && !$type instanceof NullType) {
			$type = \PHPStan\Type\TypeCombinator::removeNull($type);
		}

		if ($type->isClonable()) {
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

}
