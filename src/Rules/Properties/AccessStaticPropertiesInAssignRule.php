<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class AccessStaticPropertiesInAssignRule implements Rule
{

	/** @var \PHPStan\Rules\Properties\AccessStaticPropertiesRule */
	private $accessStaticPropertiesRule;

	public function __construct(AccessStaticPropertiesRule $accessStaticPropertiesRule)
	{
		$this->accessStaticPropertiesRule = $accessStaticPropertiesRule;
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
		if (!$node->var instanceof Node\Expr\StaticPropertyFetch) {
			return [];
		}

		return $this->accessStaticPropertiesRule->processNode($node->var, $scope);
	}

}
