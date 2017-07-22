<?php declare(strict_types = 1);

namespace PHPStan\Cache;

class FileCacheStorage implements CacheStorage
{

	/** @var string */
	private $directory;

	public function __construct(string $directory)
	{
		$this->directory = $directory;

		if (@mkdir($this->directory) && !is_dir($this->directory)) {
			throw new \InvalidArgumentException(sprintf('Directory "%s" doesn\'t exist.', $this->directory));
		}
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function load(string $key)
	{
		return (function (string $key) {
			$filePath = $this->getFilePath($key);
			return is_file($filePath) ? require $this->getFilePath($key) : null;
		})($key);
	}

	/**
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function save(string $key, $data): bool
	{
		$writtenBytes = @file_put_contents(
			$this->getFilePath($key),
			sprintf("<?php declare(strict_types = 1);\n\nreturn %s;", var_export($data, true))
		);
		return $writtenBytes !== false;
	}

	private function getFilePath(string $key): string
	{
		return sprintf('%s/%s.php', $this->directory, preg_replace('~[^-\\w]~', '_', $key));
	}

}
