<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionDefinitionCheck */
	private $check;

	public function __construct(FunctionDefinitionCheck $check)
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
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->check->checkFunction(
			$node,
			$scope,
			sprintf(
				'Parameter $%%s of method %s::%s() has invalid typehint type %%s.',
				$scope->getClassReflection()->getDisplayName(),
				$node->name->name
			),
			sprintf(
				'Return typehint of method %s::%s() has invalid type %%s.',
				$scope->getClassReflection()->getDisplayName(),
				$node->name->name
			)
		);
	}

}
