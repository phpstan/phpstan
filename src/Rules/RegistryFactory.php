<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\DependencyInjection\Container;

class RegistryFactory
{

	public const RULE_TAG = 'phpstan.rules.rule';

	/** @var Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): Registry
	{
		return new Registry(
			$this->container->getServicesByTag(self::RULE_TAG)
		);
	}

}
