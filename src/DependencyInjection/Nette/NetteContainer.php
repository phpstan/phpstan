<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection\Nette;

use PHPStan\DependencyInjection\Container;

class NetteContainer implements Container
{

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(\Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param string $serviceName
	 * @return mixed
	 */
	public function getService(string $serviceName)
	{
		return $this->container->getService($serviceName);
	}

	/**
	 * @param string $className
	 * @return mixed
	 */
	public function getByType(string $className)
	{
		return $this->container->getByType($className);
	}

	/**
	 * @param string $tagName
	 * @return mixed[]
	 */
	public function getServicesByTag(string $tagName): array
	{
		return $this->tagsToServices($this->container->findByTag($tagName));
	}

	/**
	 * @param string $parameterName
	 * @return mixed
	 */
	public function getParameter(string $parameterName)
	{
		return $this->container->parameters[$parameterName] ?? null;
	}

	/**
	 * @param mixed[] $tags
	 * @return mixed[]
	 */
	private function tagsToServices(array $tags): array
	{
		return array_map(function (string $serviceName) {
			return $this->getService($serviceName);
		}, array_keys($tags));
	}

}
