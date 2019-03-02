<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class AccessPropertiesInAssignRule implements Rule
{

	/** @var \PHPStan\Rules\Properties\AccessPropertiesRule */
	private $accessPropertiesRule;

	public function __construct(AccessPropertiesRule $accessPropertiesRule)
	{
		$this->accessPropertiesRule = $accessPropertiesRule;
	}

	public function getNodeType(): string
	{
		return Node\Expr\Assign::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Assign $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->var instanceof Node\Expr\PropertyFetch) {
			return [];
		}

		return $this->accessPropertiesRule->processNode($node->var, $scope);
	}

}
