<?php declare(strict_types = 1);

namespace PHPStan\Configuration;

use PHPStan\Cache\Cache;

class ConfigurationChangeDetector
{

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var string */
	private $configurationVersion;

	/**
	 * @param \PHPStan\Cache\Cache $cache
	 * @param mixed[] $configurationParameters
	 */
	public function __construct(Cache $cache, array $configurationParameters)
	{
		$this->cache = $cache;
		$this->configurationVersion = md5(serialize($configurationParameters));
	}

	public function hasConfigurationChanged(): bool
	{
		return $this->cache->load($this->getCacheKey()) !== $this->configurationVersion;
	}

	public function saveToCache()
	{
		$this->cache->save($this->getCacheKey(), $this->configurationVersion);
	}

	private function getCacheKey(): string
	{
		return 'configurationVersion';
	}

}
