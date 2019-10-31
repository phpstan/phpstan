<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @phpstan-template TNodeType of \PhpParser\Node
 */
interface Rule
{

	/**
	 * @phpstan-return class-string<TNodeType>
	 * @return string
	 */
	public function getNodeType(): string;

	/**
	 * @phpstan-param TNodeType $node
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array;

}
