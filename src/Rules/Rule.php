<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @template TNodeType of \PhpParser\Node
 */
interface Rule
{

	/**
	 * @return class-string<TNodeType>
	 */
	public function getNodeType(): string;

	/**
	 * @param TNodeType $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array;

}
