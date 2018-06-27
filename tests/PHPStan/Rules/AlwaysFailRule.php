<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class AlwaysFailRule implements \PHPStan\Rules\Rule
{

    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return ['Fail.'];
    }
}
