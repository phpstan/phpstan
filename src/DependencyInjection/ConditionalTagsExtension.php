<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

class ConditionalTagsExtension extends \Nette\DI\CompilerExtension
{

	public function beforeCompile(): void
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		foreach ($config as $type => $tags) {
			foreach ($builder->findByType($type) as $service) {
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
