<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Nette\DI\Container;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory;

class TypeSpecifierFactory
{

	public const FUNCTION_TYPE_SPECIFYING_EXTENSION_TAG = 'phpstan.typeSpecifier.functionTypeSpecifyingExtension';
	public const METHOD_TYPE_SPECIFYING_EXTENSION_TAG = 'phpstan.typeSpecifier.methodTypeSpecifyingExtension';
	public const STATIC_METHOD_TYPE_SPECIFYING_EXTENSION_TAG = 'phpstan.typeSpecifier.staticMethodTypeSpecifyingExtension';

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): TypeSpecifier
	{
		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->container->getService($serviceName);
			}, array_keys($tags));
		};

		$typeSpecifier = new TypeSpecifier(
			$this->container->getByType(Standard::class),
			$this->container->getByType(Broker::class),
			$tagToService($this->container->findByTag(self::FUNCTION_TYPE_SPECIFYING_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(self::METHOD_TYPE_SPECIFYING_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(self::STATIC_METHOD_TYPE_SPECIFYING_EXTENSION_TAG))
		);

		foreach (array_merge(
			$tagToService($this->container->findByTag(BrokerFactory::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(BrokerFactory::METHODS_CLASS_REFLECTION_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(BrokerFactory::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(BrokerFactory::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG)),
			$tagToService($this->container->findByTag(BrokerFactory::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG))
		) as $extension) {
			if (!($extension instanceof TypeSpecifierAwareExtension)) {
				continue;
			}

			$extension->setTypeSpecifier($typeSpecifier);
		}

		return $typeSpecifier;
	}

}
