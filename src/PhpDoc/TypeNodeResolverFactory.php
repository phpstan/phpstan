<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\DependencyInjection\Container;

class TypeNodeResolverFactory
{

	public const EXTENSION_TAG = 'phpstan.phpDoc.typeNodeResolverExtension';

	/** @var \PHPStan\DependencyInjection\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): TypeNodeResolver
	{
		return new TypeNodeResolver(
			$this->container->getServicesByTag(self::EXTENSION_TAG)
		);
	}

}
