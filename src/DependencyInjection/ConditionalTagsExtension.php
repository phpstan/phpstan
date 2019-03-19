<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

class ConditionalTagsExtension extends \Nette\DI\CompilerExtension
{

	public function beforeCompile(): void
	{
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
