<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInClosureTypehintsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionDefinitionCheck */
	private $check;

	public function __construct(FunctionDefinitionCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return Closure::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Closure $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->check->checkFunction(
			$node,
			$scope,
			'Parameter $%s of anonymous function has invalid typehint type %s.',
			'Return typehint of anonymous function has invalid type %s.'
		);
	}

}
