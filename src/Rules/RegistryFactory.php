<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RegistryFactory
{

	public const RULE_TAG = 'phpstan.rules.rule';

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(\Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	public function create(): Registry
	{
		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->container->getService($serviceName);
			}, array_keys($tags));
		};

		return new Registry(
			$tagToService($this->container->findByTag(self::RULE_TAG))
		);
	}

}
