<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Type\ObjectType;

class ClosureReturnTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionReturnTypeCheck */
	private $returnTypeCheck;

	public function __construct(FunctionReturnTypeCheck $returnTypeCheck)
	{
		$this->returnTypeCheck = $returnTypeCheck;
	}

	public function getNodeType(): string
	{
		return Return_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Return_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInAnonymousFunction()) {
			return [];
		}

		/** @var \PHPStan\Type\Type $returnType */
		$returnType = $scope->getAnonymousFunctionReturnType();
		$generatorType = new ObjectType(\Generator::class);

		return $this->returnTypeCheck->checkReturnType(
			$scope,
			$returnType,
			$node->expr,
			'Anonymous function should return %s but empty return statement found.',
			'Anonymous function with return type void returns %s but should not return anything.',
			'Anonymous function should return %s but returns %s.',
			$generatorType->isSuperTypeOf($returnType)->yes()
		);
	}

}
