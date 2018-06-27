<?php declare(strict_types = 1);

namespace PHPStan\Tests;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\StringType;

class AssertionClassMethodTypeSpecifyingExtension implements MethodTypeSpecifyingExtension
{

    /** @var bool|null */
    private $nullContext;

    public function __construct(?bool $nullContext)
    {
        $this->nullContext = $nullContext;
    }

    public function getClass(): string
    {
        return AssertionClass::class;
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context
    ): bool {
        if ($this->nullContext === null) {
            return $methodReflection->getName() === 'assertString';
        }

        if ($this->nullContext) {
            return $methodReflection->getName() === 'assertString' && $context->null();
        }

        return $methodReflection->getName() === 'assertString' && !$context->null();
    }

    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        return new SpecifiedTypes(['$foo' => [$node->args[0]->value, new StringType()]]);
    }
}
