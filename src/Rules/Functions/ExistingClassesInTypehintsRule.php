<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
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
		return Function_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Function_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->check->checkFunction(
			$node,
			$scope,
			sprintf(
				'Parameter $%%s of function %s() has invalid typehint type %%s.',
				(string) $node->namespacedName
			),
			sprintf(
				'Return typehint of function %s() has invalid type %%s.',
				(string) $node->namespacedName
			)
		);
	}

}
