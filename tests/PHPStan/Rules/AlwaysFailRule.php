<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Node;

class AlwaysFailRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return 'PHPParser_Node_Expr_FuncCall';
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		return ['Fail'];
	}

}
