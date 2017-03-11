<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;
use DI\Container;

class FunctionReflectionFactoryDI implements FunctionReflectionFactory
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(
        \ReflectionFunction $reflection,
        array $phpDocParameterTypes,
        Type $phpDocReturnType = null
    ): FunctionReflection {
    
        return $this->container->make(FunctionReflection::class, compact([
            'reflection', 'phpDocParameterTypes', 'phpDocReturnType',
        ]));
    }
}
