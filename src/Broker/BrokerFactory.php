<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use Interop\Container\ContainerInterface;

class BrokerFactory
{
    const PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.propertiesClassReflectionExtension';
    const METHODS_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.methodsClassReflectionExtension';
    const DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicMethodReturnTypeExtension';
    const DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicStaticMethodReturnTypeExtension';

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): Broker
    {
        $tagToService = function (array $tags) {
            return array_map(function (string $serviceName) {
                return $this->container->getService($serviceName);
            }, array_keys($tags));
        };

        $phpClassReflectionExtension = $this->container->get(PhpClassReflectionExtension::class);
        $annotationsMethodsClassReflectionExtension = $this->container->get(AnnotationsMethodsClassReflectionExtension::class);
        $annotationsPropertiesClassReflectionExtension = $this->container->get(AnnotationsPropertiesClassReflectionExtension::class);
        $phpDefectClassReflectionExtension = $this->container->get(PhpDefectClassReflectionExtension::class);
        // todo find by interface not tag

        return new Broker(
            // array_merge([$phpClassReflectionExtension, $annotationsPropertiesClassReflectionExtension, $phpDefectClassReflectionExtension], $tagToService($this->container->findByTag(self::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG))),
            // array_merge([$phpClassReflectionExtension, $annotationsMethodsClassReflectionExtension], $tagToService($this->container->findByTag(self::METHODS_CLASS_REFLECTION_EXTENSION_TAG))),
            array_merge([$phpClassReflectionExtension, $annotationsPropertiesClassReflectionExtension, $phpDefectClassReflectionExtension], []),
            array_merge([$phpClassReflectionExtension, $annotationsMethodsClassReflectionExtension], []),
            [], //$tagToService($this->container->findByTag(self::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
            [], //$tagToService($this->container->findByTag(self::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
            $this->container->get(FunctionReflectionFactory::class),
            $this->container->get(FileTypeMapper::class)
        );
    }
}
