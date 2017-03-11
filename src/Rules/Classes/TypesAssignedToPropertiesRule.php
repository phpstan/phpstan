<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class TypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

    /** @var \PHPStan\Broker\Broker */
    private $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @param \PhpParser\Node\Expr\Assign $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            !($node->var instanceof Node\Expr\PropertyFetch)
            && !($node->var instanceof Node\Expr\StaticPropertyFetch)
        ) {
            return [];
        }

        /** @var \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch */
        $propertyFetch = $node->var;

        $propertyType = $scope->getType($propertyFetch);
        $assignedValueType = $scope->getType($node->expr);

        if (!$propertyType->accepts($assignedValueType)) {
            $propertyDescription = $this->describeProperty($propertyFetch, $scope);
            if ($propertyDescription === null) {
                return [];
            }

            return [
                sprintf(
                    '%s (%s) does not accept %s.',
                    $propertyDescription,
                    $propertyType->describe(),
                    $assignedValueType->describe()
                ),
            ];
        }

        return [];
    }

    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     * @param \PHPStan\Analyser\Scope $scope
     * @return string|null
     */
    private function describeProperty($propertyFetch, Scope $scope)
    {
        if ($propertyFetch instanceof Node\Expr\PropertyFetch) {
            if (!is_string($propertyFetch->name)) {
                return null;
            }
            $propertyHolderType = $scope->getType($propertyFetch->var);
            if ($propertyHolderType->getClass() === null) {
                return null;
            }

            $property = $this->findPropertyReflection($propertyHolderType->getClass(), $propertyFetch->name, $scope);
            if ($property === null) {
                return null;
            }

            return sprintf('Property %s::$%s', $property->getDeclaringClass()->getName(), $propertyFetch->name);
        } elseif ($propertyFetch instanceof Node\Expr\StaticPropertyFetch) {
            if (
                !($propertyFetch->class instanceof Node\Name)
                || !is_string($propertyFetch->name)
            ) {
                return null;
            }

            $property = $this->findPropertyReflection($scope->resolveName($propertyFetch->class), $propertyFetch->name, $scope);
            if ($property === null) {
                return null;
            }

            return sprintf('Static property %s::$%s', $property->getDeclaringClass()->getName(), $propertyFetch->name);
        }

        return null;
    }

    /**
     * @param string $className
     * @param string $propertyName
     * @param \PHPStan\Analyser\Scope $scope
     * @return \PHPStan\Reflection\PropertyReflection|null
     */
    private function findPropertyReflection(string $className, string $propertyName, Scope $scope)
    {
        if (!$this->broker->hasClass($className)) {
            return null;
        }
        $propertyClass = $this->broker->getClass($className);
        if (!$propertyClass->hasProperty($propertyName)) {
            return null;
        }

        return $propertyClass->getProperty($propertyName, $scope);
    }
}
