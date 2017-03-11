<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;

class DefaultValueTypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{
    public function getNodeType(): string
    {
        return Property::class;
    }

    /**
     * @param \PhpParser\Node\Stmt\Property $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        $errors = [];
        foreach ($node->props as $property) {
            if ($property->default === null) {
                continue;
            }

            if ($property->default instanceof Node\Expr\ConstFetch && (string) $property->default->name === 'null') {
                continue;
            }

            $propertyReflection = $classReflection->getProperty($property->name, $scope);
            $propertyType = $propertyReflection->getType();
            $defaultValueType = $scope->getType($property->default);
            if ($propertyType->accepts($defaultValueType)) {
                continue;
            }

            $errors[] = sprintf(
                '%s %s::$%s (%s) does not accept default value of type %s.',
                $node->isStatic() ? 'Static property' : 'Property',
                $classReflection->getName(),
                $property->name,
                $propertyType->describe(),
                $defaultValueType->describe()
            );
        }

        return $errors;
    }
}
