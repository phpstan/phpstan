<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use Nette\DI\Container;

class TypeNodeResolverFactory
{

	public const EXTENSION_TAG = 'phpstan.phpDoc.typeNodeResolverExtension';

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): TypeNodeResolver
	{
		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->container->getService($serviceName);
			}, array_keys($tags));
		};

		return new TypeNodeResolver(
			$tagToService($this->container->findByTag(self::EXTENSION_TAG))
		);
	}

}
