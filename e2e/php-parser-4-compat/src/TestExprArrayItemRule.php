<?php declare(strict_types = 1);

namespace App;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<Node\Expr\ArrayItem>
 */
class TestExprArrayItemRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\ArrayItem::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message(sprintf('Array item has value: %s', $scope->getType($node->value)->describe(VerbosityLevel::precise())))->identifier('test.arrayItemValue')->build(),
        ];
    }


}
