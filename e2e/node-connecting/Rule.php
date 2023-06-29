<?php

namespace NodeConnectingRule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Echo_;
use PHPStan\Analyser\Scope;

/** @implements \PHPStan\Rules\Rule<Echo_> */
class Rule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Echo_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		return [
			sprintf(
				'Parent: %s, previous: %s, next: %s',
				get_class($node->getAttribute('parent')),
				get_class($node->getAttribute('previous')),
				get_class($node->getAttribute('next'))
			),
		];
	}


}
