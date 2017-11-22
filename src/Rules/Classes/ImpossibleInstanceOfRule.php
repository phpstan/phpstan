<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;

class ImpossibleInstanceOfRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkAlwaysTrueInstanceof;

	public function __construct(bool $checkAlwaysTrueInstanceof)
	{
		$this->checkAlwaysTrueInstanceof = $checkAlwaysTrueInstanceof;
	}

	public function getNodeType(): string
	{
		return Node\Expr\Instanceof_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Instanceof_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->class instanceof Node\Name) {
			$className = $scope->resolveName($node->class);
			$type = new ObjectType($className);
		} else {
			$type = $scope->getType($node->class);
		}

		$expressionType = $scope->getType($node->expr);
		$isExpressionObject = (new ObjectWithoutClassType())->isSuperTypeOf($expressionType);
		if (!$isExpressionObject->no() && $type instanceof StringType) {
			return [];
		}

		$isSuperType = $type->isSuperTypeOf($expressionType)
			->and($isExpressionObject);

		if ($isSuperType->no()) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to false.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		} elseif ($isSuperType->yes() && $this->checkAlwaysTrueInstanceof) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to true.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		}

		return [];
	}

}
