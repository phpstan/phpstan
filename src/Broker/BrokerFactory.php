<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Php\AllArgumentBasedFunctionReturnTypeExtension;
use PHPStan\Type\Php\ArgumentBasedArrayFunctionReturnTypeExtension;
use PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
use PHPStan\Type\Php\CallbackBasedArrayFunctionReturnTypeExtension;
use PHPStan\Type\Php\CallbackBasedFunctionReturnTypeExtension;

class BrokerFactory
{

	const PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.propertiesClassReflectionExtension';
	const METHODS_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.methodsClassReflectionExtension';
	const DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicMethodReturnTypeExtension';
	const DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicStaticMethodReturnTypeExtension';
	const DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicFunctionReturnTypeExtension';

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(\Nette\DI\Container $container)
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

		$phpClassReflectionExtension = $this->container->getByType(PhpClassReflectionExtension::class);
		$annotationsMethodsClassReflectionExtension = $this->container->getByType(AnnotationsMethodsClassReflectionExtension::class);
		$annotationsPropertiesClassReflectionExtension = $this->container->getByType(AnnotationsPropertiesClassReflectionExtension::class);
		$phpDefectClassReflectionExtension = $this->container->getByType(PhpDefectClassReflectionExtension::class);
		$dynamicFunctionReturnTypeExtensions = array_merge(
			$tagToService($this->container->findByTag(self::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG)),
			[
				$this->container->getByType(AllArgumentBasedFunctionReturnTypeExtension::class),
				$this->container->getByType(ArgumentBasedArrayFunctionReturnTypeExtension::class),
				$this->container->getByType(ArgumentBasedFunctionReturnTypeExtension::class),
				$this->container->getByType(CallbackBasedArrayFunctionReturnTypeExtension::class),
				$this->container->getByType(CallbackBasedFunctionReturnTypeExtension::class),
			]
		);

		return new Broker(
			array_merge([$phpClassReflectionExtension, $phpDefectClassReflectionExtension], $tagToService($this->container->findByTag(self::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG)), [$annotationsPropertiesClassReflectionExtension]),
			array_merge([$phpClassReflectionExtension], $tagToService($this->container->findByTag(self::METHODS_CLASS_REFLECTION_EXTENSION_TAG)), [$annotationsMethodsClassReflectionExtension]),
			$tagToService($this->container->findByTag(self::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(self::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
			$dynamicFunctionReturnTypeExtensions,
			$this->container->getByType(FunctionReflectionFactory::class),
			$this->container->getByType(FileTypeMapper::class)
		);
	}

}
