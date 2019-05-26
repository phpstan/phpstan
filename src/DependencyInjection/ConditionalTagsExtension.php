<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette;
use Nette\Schema\Expect;
use PHPStan\Analyser\TypeSpecifierFactory;
use PHPStan\Broker\BrokerFactory;
use PHPStan\PhpDoc\TypeNodeResolverFactory;
use PHPStan\Rules\RegistryFactory;

class ConditionalTagsExtension extends \Nette\DI\CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		$bool = Expect::bool();
		return Expect::arrayOf(Expect::structure([
			BrokerFactory::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG => $bool,
			BrokerFactory::METHODS_CLASS_REFLECTION_EXTENSION_TAG => $bool,
			BrokerFactory::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG => $bool,
			BrokerFactory::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG => $bool,
			BrokerFactory::DYNAMIC_FUNCTION_RETURN_TYPE_EXTENSION_TAG => $bool,
			BrokerFactory::OPERATOR_TYPE_SPECIFYING_EXTENSION_TAG => $bool,
			RegistryFactory::RULE_TAG => $bool,
			TypeNodeResolverFactory::EXTENSION_TAG => $bool,
			TypeSpecifierFactory::FUNCTION_TYPE_SPECIFYING_EXTENSION_TAG => $bool,
			TypeSpecifierFactory::METHOD_TYPE_SPECIFYING_EXTENSION_TAG => $bool,
			TypeSpecifierFactory::STATIC_METHOD_TYPE_SPECIFYING_EXTENSION_TAG => $bool,
		])->min(1));
	}

	public function beforeCompile(): void
	{
		/** @var mixed[] $config */
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		foreach ($config as $type => $tags) {
			$services = $builder->findByType($type);
			if (count($services) === 0) {
				throw new \PHPStan\ShouldNotHappenException(sprintf('No services of type "%s" found.', $type));
			}
			foreach ($services as $service) {
				foreach ($tags as $tag => $parameter) {
					if ((bool) $parameter) {
						$service->addTag($tag);
						continue;
					}
				}
			}
		}
	}

}
