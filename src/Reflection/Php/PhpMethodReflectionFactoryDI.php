<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use DI\Container;

class PhpMethodReflectionFactoryDI implements PhpMethodReflectionFactory
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \PHPStan\Reflection\ClassReflection $declaringClass
     * @param \ReflectionMethod $reflection
     * @param \PHPStan\Type\Type[] $phpDocParameterTypes
     * @param \PHPStan\Type\Type|null $phpDocReturnType
     * @return \PHPStan\Reflection\Php\PhpMethodReflection
     */
    public function create(
        ClassReflection $declaringClass,
        \ReflectionMethod $reflection,
        array $phpDocParameterTypes,
        Type $phpDocReturnType = null
    ): PhpMethodReflection
    {
        return $this->container->make(PhpMethodReflection::class, compact([
            'declaringClass', 'reflection', 'phpDocParameterTypes', 'phpDocReturnType'
        ]));
    }
}
