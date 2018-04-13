<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

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
		$instanceofType = $scope->getType($node);

		if (!$instanceofType instanceof ConstantBooleanType) {
			return [];
		}

		$expressionType = $scope->getType($node->expr);
		if ($node->class instanceof Node\Name) {
			$className = $scope->resolveName($node->class);
			$type = new ObjectType($className);
		} else {
			$type = $scope->getType($node->class);
		}

		if (!$instanceofType->getValue()) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to false.',
					$expressionType->describe(VerbosityLevel::typeOnly()),
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		} elseif ($this->checkAlwaysTrueInstanceof) {
			return [
				sprintf(
					'Instanceof between %s and %s will always evaluate to true.',
					$expressionType->describe(VerbosityLevel::typeOnly()),
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
