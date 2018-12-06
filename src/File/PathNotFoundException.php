<?php declare(strict_types = 1);

namespace PHPStan\File;

class PathNotFoundException extends \Exception
{

	/** @var string */
	private $path;

	public function __construct(string $path)
	{
		parent::__construct(sprintf('Path %s does not exist', $path));
		$this->path = $path;
	}

	public function getPath(): string
	{
		return $this->path;
	}

}
