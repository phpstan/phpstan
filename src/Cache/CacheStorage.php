<?php declare(strict_types = 1);

namespace PHPStan\Cache;

interface CacheStorage
{

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function load(string $key);

	/**
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function save(string $key, $data): bool;

}
