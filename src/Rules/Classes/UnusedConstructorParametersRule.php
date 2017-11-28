<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\UnusedFunctionParametersCheck;

class UnusedConstructorParametersRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\UnusedFunctionParametersCheck */
	private $check;

	public function __construct(UnusedFunctionParametersCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name !== '__construct' || $node->stmts === null) {
			return [];
		}

		if (count($node->params) === 0) {
			return [];
		}

		$message = sprintf('Constructor of class %s has an unused parameter $%%s.', $scope->getClassReflection()->getDisplayName());
		if ($scope->getClassReflection()->isAnonymous()) {
			$message = 'Constructor of an anonymous class has an unused parameter $%s.';
		}

		return $this->check->getUnusedParameters(
			array_map(function (Param $parameter): string {
				return $parameter->name;
			}, $node->params),
			$node->stmts,
			$message
		);
	}

}
