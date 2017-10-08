<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;

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
			$className = (string) $node->class;
			$type = new ObjectType($className);
		} else {
			$type = $scope->getType($node->class);
		}

		$expressionType = $scope->getType($node->expr);
		$isSuperset = $type->isSupersetOf($expressionType)
			->and((new ObjectWithoutClassType())->isSupersetOf($expressionType));

		if ($isSuperset->no()) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to false.',
					$expressionType->describe(),
					$type->describe()
				),
			];
		} elseif ($isSuperset->yes() && $this->checkAlwaysTrueInstanceof) {
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
