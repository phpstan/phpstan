<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\RestrictedUsage\RestrictedUsage;
use PHPStan\ShouldNotHappenException;
use function array_map;
use function count;
use function sprintf;

/**
 * @implements Collector<Node\Expr\StaticCall, array{identifiers: non-empty-list<string>, class: string, file: string, line: int}>
 */
class RestrictedUsageCollector implements Collector
{

    public function getNodeType(): string
    {
        return Node\Expr\StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        if (!$node->class instanceof Node\Name) {
            return null;
        }

        if ($node->name->toLowerString() !== 'create') {
            return null;
        }

        if ($node->class->toString() !== RestrictedUsage::class) {
            return null;
        }

        $args = $node->getArgs();
        if (!isset($args[1])) {
            return null;
        }

        $identifier = $scope->getType($args[1]->value);
        if (count($identifier->getConstantStrings()) === 0) {
            throw new ShouldNotHappenException(sprintf('Unknown identifier in %s on line %d', $scope->getFile(), $args[1]->getStartLine()));
        }

        if (!$scope->isInClass()) {
            return null;
        }

        return [
            'identifiers' => array_map(static fn ($type) => $type->getValue(), $identifier->getConstantStrings()),
            'class' => $scope->getClassReflection()->getName(),
            'file' => $scope->getFile(),
            'line' => $args[1]->getStartLine(),
        ];
    }

}
