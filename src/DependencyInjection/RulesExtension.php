<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use PHPStan\Rules\RegistryFactory;

class RulesExtension extends \Nette\DI\CompilerExtension
{

	public function loadConfiguration(): void
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		foreach ($config as $key => $rule) {
			$builder->addDefinition($this->prefix((string) $key))
				->setFactory($rule)
				->setAutowired(false)
				->addTag(RegistryFactory::RULE_TAG);
		}
	}

}
