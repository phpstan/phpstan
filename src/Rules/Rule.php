<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Node;

interface Rule
{

	/**
	 * @return string Class implementing \PhpParser\Node
	 */
	public function getNodeType(): string;

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[] errors
	 */
	public function processNode(Node $node): array;

}
