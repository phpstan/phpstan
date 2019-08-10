<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\FunctionDefinitionCheck;
use PHPStan\Rules\RuleError;

class ExistingClassesInArrowFunctionTypehintsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionDefinitionCheck */
	private $check;

	public function __construct(FunctionDefinitionCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return Node\Expr\ArrowFunction::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrowFunction $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->check->checkAnonymousFunction(
			$node->getParams(),
			$node->getReturnType(),
			'Parameter $%s of anonymous function has invalid typehint type %s.',
			'Return typehint of anonymous function has invalid type %s.'
		);
	}

}
