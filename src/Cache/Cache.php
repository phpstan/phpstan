<?php declare(strict_types = 1);

namespace PHPStan\Cache;

class Cache
{

	/** @var \PHPStan\Cache\CacheStorage */
	private $storage;

	public function __construct(CacheStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function load(string $key)
	{
		return $this->storage->load($key);
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function save(string $key, $data): bool
	{
		return $this->storage->save($key, $data);
	}

}
