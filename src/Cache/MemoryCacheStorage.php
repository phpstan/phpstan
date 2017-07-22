<?php declare(strict_types = 1);

namespace PHPStan\Cache;

class MemoryCacheStorage implements CacheStorage
{

	/** @var mixed[] */
	private $storage = [];

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function load(string $key)
	{
		return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function save(string $key, $data): bool
	{
		$this->storage[$key] = $data;
		return true;
	}

}
