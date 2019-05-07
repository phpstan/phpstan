<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\DependencyInjection\Container;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Type\FileTypeMapper;

class BrokerFactory
{

	public const PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.propertiesClassReflectionExtension';
	public const METHODS_CLASS_REFLECTION_EXTENSION_TAG = 'phpstan.broker.methodsClassReflectionExtension';
	public const DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicMethodReturnTypeExtension';
	public const DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicStaticMethodReturnTypeExtension';
	public const DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG = 'phpstan.broker.dynamicFunctionReturnTypeExtension';
	public const OPERATOR_TYPE_SPECIFYING_EXTENSION_TAG = 'phpstan.broker.operatorTypeSpecifyingExtension';

	/** @var \PHPStan\DependencyInjection\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): Broker
	{
		$phpClassReflectionExtension = $this->container->getByType(PhpClassReflectionExtension::class);
		$annotationsMethodsClassReflectionExtension = $this->container->getByType(AnnotationsMethodsClassReflectionExtension::class);
		$annotationsPropertiesClassReflectionExtension = $this->container->getByType(AnnotationsPropertiesClassReflectionExtension::class);
		$phpDefectClassReflectionExtension = $this->container->getByType(PhpDefectClassReflectionExtension::class);

		/** @var RelativePathHelper $relativePathHelper */
		$relativePathHelper = $this->container->getService('simpleRelativePathHelper');

		return new Broker(
			array_merge([$phpClassReflectionExtension, $phpDefectClassReflectionExtension], $this->container->getServicesByTag(self::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG), [$annotationsPropertiesClassReflectionExtension]),
			array_merge([$phpClassReflectionExtension], $this->container->getServicesByTag(self::METHODS_CLASS_REFLECTION_EXTENSION_TAG), [$annotationsMethodsClassReflectionExtension]),
			$this->container->getServicesByTag(self::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG),
			$this->container->getServicesByTag(self::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG),
			$this->container->getServicesByTag(self::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG),
			$this->container->getServicesByTag(self::OPERATOR_TYPE_SPECIFYING_EXTENSION_TAG),
			$this->container->getByType(FunctionReflectionFactory::class),
			$this->container->getByType(FileTypeMapper::class),
			$this->container->getByType(SignatureMapProvider::class),
			$this->container->getByType(\PhpParser\PrettyPrinter\Standard::class),
			$this->container->getByType(AnonymousClassNameHelper::class),
			$this->container->getByType(Parser::class),
			$relativePathHelper,
			$this->container->getParameter('universalObjectCratesClasses')
		);
	}

}
